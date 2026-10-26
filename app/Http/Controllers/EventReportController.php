<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReport;
use App\Notifications\NewEventReportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EventReportController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot protection
        if (!empty($request->input('honeypot'))) {
            Log::warning('Event report spam attempt detected', [
                'event_id' => $request->input('event_id'),
                'honeypot_value' => $request->input('honeypot'),
                'ip' => $request->ip(),
            ]);

            // Return success to avoid revealing the honeypot
            return response()->json([
                'success' => true,
                'message' => 'Your report has been submitted successfully!'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'reporter_name' => 'required_without:organizer_id|string|max:255',
            'reporter_email' => 'required_without:organizer_id|email|max:255',
            'report_type' => 'required|in:bug,content_issue,spam,inappropriate,other',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:2000',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check your input.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Event::findOrFail($request->event_id);
            
            // Get organizer_id from authenticated user's organizer relationship or null for public reports
            $organizerId = null;
            if (auth()->check()) {
                $user = auth()->user();
                // If user has organizer relationship, get the organizer_id
                $organizer = $user->organizers()->first();
                $organizerId = $organizer?->id;
            }

            // Create the event report
            $eventReport = EventReport::create([
                'event_id' => $event->id,
                'organizer_id' => $organizerId,
                'reporter_name' => auth()->check() ? null : $request->reporter_name,
                'reporter_email' => auth()->check() ? null : $request->reporter_email,
                'report_type' => $request->report_type,
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => $request->priority ?? 'medium',
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'url' => $request->header('referer'),
                    'submitted_via' => 'web_form',
                    'auth_user_id' => auth()->id(), // Keep track of which user submitted
                ],
            ]);

            // Notify the event organizer
            try {
                $organizer = $event->organizer;
                if ($organizer) {
                    // Get all users associated with this organizer
                    $organizerUsers = $organizer->users;
                    
                    foreach ($organizerUsers as $user) {
                        $user->notify(new NewEventReportNotification($eventReport));
                    }
                    
                    $eventReport->markOrganizerNotified();
                    
                    Log::info('Organizer notified of new event report', [
                        'event_report_id' => $eventReport->id,
                        'event_id' => $event->id,
                        'organizer_id' => $organizer->id,
                        'notified_users' => $organizerUsers->count(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify organizer of event report', [
                    'error' => $e->getMessage(),
                    'event_report_id' => $eventReport->id,
                    'event_id' => $event->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Your report has been submitted successfully. The event organizer will be notified.',
                'report_id' => $eventReport->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create event report', [
                'error' => $e->getMessage(),
                'event_id' => $request->event_id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to submit your report at this time. Please try again later.',
            ], 500);
        }
    }

    public function show(EventReport $eventReport)
    {
        // Ensure user can view this report (organizer or admin)
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is organizer of the event
            if ($eventReport->event->organizer->users->contains($user)) {
                return response()->json([
                    'success' => true,
                    'report' => $eventReport->load(['event', 'organizer']),
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to view this report.',
        ], 403);
    }
}
