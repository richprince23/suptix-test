<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientCreditsException;
use App\Mail\RsvpConfirmationMail;
use App\Mail\RsvpNotificationMail;
use App\Mail\WaitlistConfirmationMail;
use App\Models\Event;
use App\Models\PendingRsvpNotification;
use App\Models\RSVP;
use App\Models\RsvpResponse;
use App\Models\RsvpWaitlist;
use App\Services\EventCreditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Validator;

class RsvpController extends Controller
{
    public function __construct(protected EventCreditService $creditService) {}

    public function show(Event $event)
    {
        return redirect()->route('events.show', $event);
    }

    public function checkExistingRsvp(Request $request, Event $event)
    {
        $email = $request->get('email');

        if (! $email) {
            return response()->json(['exists' => false]);
        }

        $existingRsvp = RSVP::where('email', $email)->where('event_id', $event->id)->first();

        if ($existingRsvp) {
            return response()->json([
                'exists' => true,
                'rsvp' => [
                    'id' => $existingRsvp->id,
                    'guest_name' => $existingRsvp->guest_name,
                    'email' => $existingRsvp->email,
                    'phone' => $existingRsvp->phone,
                    'referral_source' => $existingRsvp->referral_source,
                    'notes' => $existingRsvp->notes,
                    'created_at' => $existingRsvp->created_at->format('F j, Y \a\t g:i A'),
                ],
                'message' => 'You have already registered for this event. You can update your information below.',
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function reserveSpot(Request $request, Event $event)
    {
        if (! in_array($event->status, ['published', 'live'])) {
            return back()->withErrors(['event_status' => 'This event is no longer accepting RSVPs.']);
        }

        $deadline = $event->rsvp_closes_at
            ? $event->rsvp_closes_at
            : Carbon::createFromFormat('Y-m-d H:i:s',
                $event->start_date->format('Y-m-d') . ' ' . ($event->start_time ?? '00:00:00')
            );

        if ($deadline->isPast()) {
            $message = $event->rsvp_closes_at
                ? 'RSVP registration has closed.'
                : 'RSVP deadline has passed. This event has already started.';

            return back()->withErrors(['rsvp_deadline' => $message]);
        }

        $validator = Validator::make($request->all(), [
            'guest_name' => 'string|required|min:3',
            'email' => 'email|required',
            'phone' => 'nullable|numeric|min_digits:10|max_digits:16',
            'referral_source' => 'nullable|string|in:Facebook,LinkedIn,Instagram,Twitter/X,Friend or Family,Flyer or Poster,Email Newsletter,Website,Other',
            'notes' => 'string|nullable|max:250',
            'honeypot' => 'nullable|string|max:0',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $rsvpData = $validator->validated();

        if (! empty($rsvpData['honeypot'])) {
            Log::warning('RSVP form spam attempt detected', [
                'email' => $rsvpData['email'],
                'honeypot_value' => $rsvpData['honeypot'],
                'event_id' => $event->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with([
                'message' => "Your seat has been reserved successfully! A confirmation email has been sent to {$rsvpData['email']}",
            ]);
        }

        unset($rsvpData['honeypot']);

        $existingRsvp = RSVP::where('email', $rsvpData['email'])->where('event_id', $event->id)->first();

        $isUpdate = false;
        if ($existingRsvp) {
            $rsvp = $existingRsvp;
            $isUpdate = true;

            Log::info('Updating existing RSVP', [
                'rsvp_id' => $rsvp->id,
                'email' => $rsvpData['email'],
                'event_id' => $event->id,
            ]);
        } else {
            if ($event->venue_capacity) {
                $currentRsvpCount = RSVP::where('event_id', $event->id)->count();
                if ($currentRsvpCount >= $event->venue_capacity) {
                    return back()->withErrors(['venue_full' => 'Sorry, this event is all booked out!']);
                }
            }

            $creditCheck = $this->creditService->canAcceptNewRsvp($event);
            if (! $creditCheck->allowed) {
                return $this->addToWaitlist($request, $event, $rsvpData);
            }

            $rsvp = new RSVP;
            $rsvp->event_id = $event->id;
            $rsvp->organizer_id = $event->organizer_id;
        }

        $rsvp->guest_name = $rsvpData['guest_name'];
        $rsvp->email = $rsvpData['email'];
        $rsvp->phone = $rsvpData['phone'] ?? null;
        $rsvp->referral_source = $rsvpData['referral_source'] ?? null;
        $rsvp->notes = $rsvpData['notes'] ?? null;

        try {
            DB::transaction(function () use ($rsvp, $event, $isUpdate) {
                $rsvp->save();
                if (! $isUpdate) {
                    $this->creditService->consumeAttendeeSlot($event, $rsvp);
                }
            });
        } catch (InsufficientCreditsException) {
            // Slots exhausted under race condition — fall back to waitlist
            return $this->addToWaitlist($request, $event, $rsvpData);
        }

        $customFields = $request->input('custom_fields', []);
        if (! empty($customFields)) {
            $formFields = $event->formFields()->get()->keyBy('id');

            foreach ($formFields->where('is_required', true) as $field) {
                $value = $customFields[$field->id] ?? null;
                if (empty($value)) {
                    return back()->withErrors(['custom_fields.' . $field->id => $field->label . ' is required.'])->withInput();
                }
            }

            RsvpResponse::where('rsvp_id', $rsvp->id)->delete();

            foreach ($customFields as $fieldId => $value) {
                if (! isset($formFields[$fieldId])) {
                    continue;
                }
                if (empty($value)) {
                    continue;
                }
                RsvpResponse::create([
                    'rsvp_id' => $rsvp->id,
                    'event_form_field_id' => (int) $fieldId,
                    'value' => is_array($value) ? implode(', ', $value) : $value,
                ]);
            }
        }

        $event->load(['organizer']);

        $emailSent = false;
        $emailCheck = $this->creditService->canSendConfirmationEmail($event);
        if ($emailCheck->allowed) {
            try {
                Mail::to($rsvp->email)->sendNow(new RsvpConfirmationMail($rsvp, $event));
                $this->creditService->consumeEmailCredit($event, $rsvp);
                $emailSent = true;
                Log::info('RSVP confirmation email sent successfully', [
                    'rsvp_id' => $rsvp->id,
                    'email' => $rsvp->email,
                    'event_id' => $event->id,
                    'is_update' => $isUpdate,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send RSVP confirmation email', [
                    'rsvp_id' => $rsvp->id,
                    'email' => $rsvp->email,
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('RSVP confirmation email skipped - credits/quota exhausted', [
                'rsvp_id' => $rsvp->id,
                'event_id' => $event->id,
                'reason' => $emailCheck->reason,
            ]);
        }

        if (! $isUpdate) {
            try {
                if ($event->organizer) {
                    $frequency = $event->organizer->rsvp_notification_frequency ?? 'daily';

                    if ($frequency === 'immediate') {
                        Mail::to($event->organizer->email)->sendNow(new RsvpNotificationMail($rsvp, $event));
                        Log::info('RSVP notification email sent immediately to organizer', [
                            'rsvp_id' => $rsvp->id,
                            'organizer_email' => $event->organizer->email,
                            'event_id' => $event->id,
                        ]);
                    } else {
                        PendingRsvpNotification::create([
                            'rsvp_id' => $rsvp->id,
                            'event_id' => $event->id,
                            'organizer_id' => $event->organizer_id,
                        ]);
                        Log::info('RSVP notification queued for digest', [
                            'rsvp_id' => $rsvp->id,
                            'organizer_id' => $event->organizer_id,
                            'event_id' => $event->id,
                            'frequency' => $frequency,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to process RSVP notification to organizer', [
                    'rsvp_id' => $rsvp->id,
                    'organizer_email' => $event->organizer->email ?? 'N/A',
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($isUpdate) {
            $message = $emailSent
                ? "Your RSVP has been updated successfully! A confirmation email has been sent to {$rsvp->email}"
                : 'Your RSVP has been updated successfully!';
        } else {
            $message = $emailSent
                ? "Your seat has been reserved successfully! A confirmation email has been sent to {$rsvp->email}"
                : 'Your seat has been reserved successfully!';
        }

        return back()->with([
            'message' => $message,
            'is_update' => $isUpdate,
        ]);
    }

    private function addToWaitlist(Request $request, Event $event, array $data): RedirectResponse
    {
        $email = $data['email'];

        $alreadyWaitlisted = RsvpWaitlist::where('email', $email)
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($alreadyWaitlisted) {
            return back()->withErrors([
                'venue_full' => 'You are already on the waitlist for this event.',
            ]);
        }

        $entry = RsvpWaitlist::create([
            'event_id'        => $event->id,
            'organizer_id'    => $event->organizer_id,
            'guest_name'      => $data['guest_name'],
            'email'           => $email,
            'phone'           => $data['phone'] ?? null,
            'referral_source' => $data['referral_source'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'custom_fields'   => $request->input('custom_fields') ?: null,
        ]);

        $event->load(['organizer']);

        try {
            Mail::to($entry->email)->sendNow(new WaitlistConfirmationMail($entry, $event));
        } catch (\Exception $e) {
            Log::error('Failed to send waitlist confirmation email', [
                'waitlist_id' => $entry->id,
                'email' => $entry->email,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Guest added to waitlist', [
            'waitlist_id' => $entry->id,
            'email' => $entry->email,
            'event_id' => $event->id,
            'position' => $entry->position,
        ]);

        return back()->with([
            'message' => "This event is currently full. You've been added to the waitlist at position #{$entry->position}. We'll notify you by email if a spot opens up.",
            'waitlisted' => true,
        ]);
    }
}
