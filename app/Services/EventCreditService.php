<?php

namespace App\Services;

use App\Exceptions\InsufficientCreditsException;
use App\Models\Event;
use App\Models\EventCreditAccount;
use App\Models\EventCreditLog;
use App\Models\EventCreditPurchase;
use App\Models\RSVP;
use App\Models\RsvpCreditSettings;
use App\Models\RsvpResponse;
use App\Models\RsvpWaitlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CreditCheckResult;

class EventCreditService
{
    public function ensureAccountExists(Event $event): EventCreditAccount
    {
        $existing = EventCreditAccount::where('event_id', $event->id)->first();
        if ($existing) {
            return $existing;
        }

        $settings = RsvpCreditSettings::current();

        return EventCreditAccount::create([
            'event_id'             => $event->id,
            'organizer_id'         => $event->organizer_id,
            'free_attendee_slots'  => $settings->free_attendee_slots_default,
            'free_email_credits'   => $settings->free_email_credits_default,
            'free_sms_credits'     => $settings->free_sms_credits_default,
        ]);
    }

    public function canAcceptNewRsvp(Event $event): CreditCheckResult
    {
        $account = EventCreditAccount::where('event_id', $event->id)->first();

        if (! $account) {
            return new CreditCheckResult(true);
        }

        $remaining = $account->getRemainingAttendeeSlots();

        if ($remaining <= 0) {
            return new CreditCheckResult(false, 'This event is fully booked. Purchase additional slots to reopen registration.');
        }

        return new CreditCheckResult(true);
    }

    public function canSendConfirmationEmail(Event $event): CreditCheckResult
    {
        $account = EventCreditAccount::where('event_id', $event->id)->first();

        if (! $account) {
            return new CreditCheckResult(true);
        }

        if ($account->getRemainingEmailCredits() <= 0) {
            return new CreditCheckResult(false, 'Email credits exhausted for this event.');
        }

        // Check shared Brevo daily quota
        $settings = RsvpCreditSettings::current();
        $today = now()->toDateString();

        $dailyTotal = EventCreditAccount::where('global_daily_email_date', $today)
            ->sum('global_daily_email_count');

        if ($dailyTotal >= $settings->brevo_daily_limit) {
            return new CreditCheckResult(false, 'Daily email quota reached.');
        }

        return new CreditCheckResult(true);
    }

    public function canSendSms(Event $event): CreditCheckResult
    {
        $account = EventCreditAccount::where('event_id', $event->id)->first();

        if (! $account) {
            return new CreditCheckResult(false, 'No credit account found for this event.');
        }

        if ($account->getRemainingSmsCredits() <= 0) {
            return new CreditCheckResult(false, 'SMS credits exhausted for this event.');
        }

        return new CreditCheckResult(true);
    }

    public function consumeAttendeeSlot(Event $event, RSVP $rsvp): void
    {
        DB::transaction(function () use ($event, $rsvp) {
            $account = EventCreditAccount::where('event_id', $event->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Re-check inside the lock to prevent race conditions
            if ($account->getRemainingAttendeeSlots() <= 0) {
                throw new \App\Exceptions\InsufficientCreditsException(
                    'This event is fully booked. Purchase additional slots to reopen registration.'
                );
            }

            $before = $account->used_attendee_slots;
            $account->increment('used_attendee_slots');

            $source = $before < $account->free_attendee_slots ? 'free_tier' : 'purchase';

            EventCreditLog::create([
                'event_id'       => $event->id,
                'rsvp_id'        => $rsvp->id,
                'type'           => 'attendee_consumed',
                'source'         => $source,
                'quantity'       => 1,
                'balance_before' => $before,
                'balance_after'  => $before + 1,
            ]);
        });
    }

    public function consumeEmailCredit(Event $event, RSVP $rsvp): void
    {
        DB::transaction(function () use ($event, $rsvp) {
            $account = EventCreditAccount::where('event_id', $event->id)
                ->lockForUpdate()
                ->firstOrFail();

            $today = now()->toDateString();

            if ($account->global_daily_email_date?->toDateString() !== $today) {
                $account->global_daily_email_date = $today;
                $account->global_daily_email_count = 0;
            }

            $before = $account->used_email_credits;
            $account->increment('used_email_credits');
            $account->increment('global_daily_email_count');
            $account->save();

            $source = $before < $account->free_email_credits ? 'free_tier' : 'purchase';

            EventCreditLog::create([
                'event_id'       => $event->id,
                'rsvp_id'        => $rsvp->id,
                'type'           => 'email_consumed',
                'source'         => $source,
                'quantity'       => 1,
                'balance_before' => $before,
                'balance_after'  => $before + 1,
            ]);
        });
    }

    public function consumeSmsCredit(Event $event, RSVP $rsvp): void
    {
        DB::transaction(function () use ($event, $rsvp) {
            $account = EventCreditAccount::where('event_id', $event->id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $account->used_sms_credits;
            $account->increment('used_sms_credits');

            $source = $before < $account->free_sms_credits ? 'free_tier' : 'purchase';

            EventCreditLog::create([
                'event_id'       => $event->id,
                'rsvp_id'        => $rsvp->id,
                'type'           => 'sms_consumed',
                'source'         => $source,
                'quantity'       => 1,
                'balance_before' => $before,
                'balance_after'  => $before + 1,
            ]);
        });
    }

    public function grantCreditsFromPurchase(EventCreditPurchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $account = EventCreditAccount::where('event_id', $purchase->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchase->attendee_slots_granted > 0) {
                $before = $account->purchased_attendee_slots;
                $account->increment('purchased_attendee_slots', $purchase->attendee_slots_granted);
                EventCreditLog::create([
                    'event_id'       => $purchase->event_id,
                    'purchase_id'    => $purchase->id,
                    'type'           => 'attendee_granted',
                    'source'         => 'purchase',
                    'quantity'       => $purchase->attendee_slots_granted,
                    'balance_before' => $before,
                    'balance_after'  => $before + $purchase->attendee_slots_granted,
                ]);
            }

            if ($purchase->email_credits_granted > 0) {
                $before = $account->purchased_email_credits;
                $account->increment('purchased_email_credits', $purchase->email_credits_granted);
                EventCreditLog::create([
                    'event_id'       => $purchase->event_id,
                    'purchase_id'    => $purchase->id,
                    'type'           => 'email_granted',
                    'source'         => 'purchase',
                    'quantity'       => $purchase->email_credits_granted,
                    'balance_before' => $before,
                    'balance_after'  => $before + $purchase->email_credits_granted,
                ]);
            }

            if ($purchase->sms_credits_granted > 0) {
                $before = $account->purchased_sms_credits;
                $account->increment('purchased_sms_credits', $purchase->sms_credits_granted);
                EventCreditLog::create([
                    'event_id'       => $purchase->event_id,
                    'purchase_id'    => $purchase->id,
                    'type'           => 'sms_granted',
                    'source'         => 'purchase',
                    'quantity'       => $purchase->sms_credits_granted,
                    'balance_before' => $before,
                    'balance_after'  => $before + $purchase->sms_credits_granted,
                ]);
            }

            $purchase->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        });
    }

    public function approveWaitlistEntry(RsvpWaitlist $entry): RSVP
    {
        return DB::transaction(function () use ($entry) {
            $account = EventCreditAccount::where('event_id', $entry->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->getRemainingAttendeeSlots() <= 0) {
                throw new InsufficientCreditsException(
                    'No attendee slots available. Purchase additional slots before approving.'
                );
            }

            $rsvp = RSVP::create([
                'event_id'        => $entry->event_id,
                'organizer_id'    => $entry->organizer_id,
                'guest_name'      => $entry->guest_name,
                'email'           => $entry->email,
                'phone'           => $entry->phone,
                'referral_source' => $entry->referral_source,
                'notes'           => $entry->notes,
                'status'          => 'attending',
            ]);

            // Transfer custom form responses
            if (! empty($entry->custom_fields)) {
                foreach ($entry->custom_fields as $fieldId => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    RsvpResponse::create([
                        'rsvp_id'             => $rsvp->id,
                        'event_form_field_id' => (int) $fieldId,
                        'value'               => is_array($value) ? implode(', ', $value) : $value,
                    ]);
                }
            }

            $before = $account->used_attendee_slots;
            $source = $before < $account->free_attendee_slots ? 'free_tier' : 'purchase';
            $account->increment('used_attendee_slots');

            EventCreditLog::create([
                'event_id'       => $entry->event_id,
                'rsvp_id'        => $rsvp->id,
                'type'           => 'attendee_consumed',
                'source'         => $source,
                'quantity'       => 1,
                'balance_before' => $before,
                'balance_after'  => $before + 1,
            ]);

            $entry->update([
                'status'      => 'approved',
                'actioned_at' => now(),
                'actioned_by' => Auth::id(),
            ]);

            return $rsvp;
        });
    }

    public function grantAdminCredits(EventCreditAccount $account, int $attendeeSlots, int $emailCredits, int $smsCredits, string $note = ''): void
    {
        DB::transaction(function () use ($account, $attendeeSlots, $emailCredits, $smsCredits, $note) {
            $account->lockForUpdate();

            if ($attendeeSlots > 0) {
                $before = $account->free_attendee_slots;
                $account->increment('free_attendee_slots', $attendeeSlots);
                EventCreditLog::create([
                    'event_id'       => $account->event_id,
                    'type'           => 'attendee_granted',
                    'source'         => 'admin_grant',
                    'quantity'       => $attendeeSlots,
                    'balance_before' => $before,
                    'balance_after'  => $before + $attendeeSlots,
                    'note'           => $note,
                ]);
            }

            if ($emailCredits > 0) {
                $before = $account->free_email_credits;
                $account->increment('free_email_credits', $emailCredits);
                EventCreditLog::create([
                    'event_id'       => $account->event_id,
                    'type'           => 'email_granted',
                    'source'         => 'admin_grant',
                    'quantity'       => $emailCredits,
                    'balance_before' => $before,
                    'balance_after'  => $before + $emailCredits,
                    'note'           => $note,
                ]);
            }

            if ($smsCredits > 0) {
                $before = $account->free_sms_credits;
                $account->increment('free_sms_credits', $smsCredits);
                EventCreditLog::create([
                    'event_id'       => $account->event_id,
                    'type'           => 'sms_granted',
                    'source'         => 'admin_grant',
                    'quantity'       => $smsCredits,
                    'balance_before' => $before,
                    'balance_after'  => $before + $smsCredits,
                    'note'           => $note,
                ]);
            }
        });
    }
}
