<?php

namespace App\Http\Controllers;

use App\Mail\DemoConfirmationMail;
use App\Mail\DemoRequestMail;
use App\Models\DemoRequest;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class DemoController extends Controller
{
    public function index()
    {
        SeoService::setRequestDemoSEO();
        return Inertia::render('RequestDemo', [
            'seo' => SeoService::requestDemo(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:10|max:16',
            'company' => 'required|string|max:255',
            'jobTitle' => 'required|string|max:255',
            'companySize' => 'required|string|max:50',
            'eventTypes' => 'required|array|min:1',
            'eventTypes.*' => 'string|max:100',
            'eventsPerYear' => 'required|string|max:50',
            'averageAttendees' => 'required|string|max:50',
            'currentSolution' => 'nullable|string|max:255',
            'demoFocus' => 'required|string|max:2000',
            'timeline' => 'required|string|max:50',
            'preferredDate' => 'nullable|date|after:today',
            'preferredTime' => 'nullable|string|max:50',
            'newsletter' => 'boolean',
            'terms' => 'accepted',
            'honeypot' => 'nullable|string|max:0', // Honeypot field must be empty
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        $demoData = $validator->validated();

        // Check honeypot - if filled, it's likely spam
        if (! empty($demoData['honeypot'])) {
            Log::warning('Demo request spam attempt detected', [
                'email' => $demoData['email'],
                'company' => $demoData['company'],
                'honeypot_value' => $demoData['honeypot'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return success to not let the bot know it was detected
            return back()->with('success', 'Demo request submitted successfully. Our sales team will contact you within 24 hours!');
        }

        // Store in database
        $demoRequest = DemoRequest::create([
            'first_name' => $demoData['firstName'],
            'last_name' => $demoData['lastName'],
            'email' => $demoData['email'],
            'phone' => $demoData['phone'],
            'company' => $demoData['company'],
            'job_title' => $demoData['jobTitle'],
            'company_size' => $demoData['companySize'],
            'event_types' => $demoData['eventTypes'],
            'events_per_year' => $demoData['eventsPerYear'],
            'average_attendees' => $demoData['averageAttendees'],
            'current_solution' => $demoData['currentSolution'],
            'demo_focus' => $demoData['demoFocus'],
            'timeline' => $demoData['timeline'],
            'preferred_date' => $demoData['preferredDate'],
            'preferred_time' => $demoData['preferredTime'],
            'newsletter' => $demoData['newsletter'] ?? false,
            'terms' => $demoData['terms'] ?? false,
            'honeypot' => null, // Don't store honeypot data
        ]);

        // Remove honeypot from data before processing
        unset($demoData['honeypot']);

        try {
            // Send email to sales team
            Mail::to(config('mail.sales_email', 'hello@suptix.live'))
                ->sendNow(new DemoRequestMail($demoData));

            // Confirmation email removed to reduce email costs
            // Sales team will follow up within 24 hours anyway
            // On-screen confirmation message is sufficient for user experience
            // Mail::to($demoData['email'])
            //     ->sendNow(new DemoConfirmationMail($demoData));

            // Log the demo request
            Log::info('Demo request submission', [
                'name' => $demoData['firstName'].' '.$demoData['lastName'],
                'email' => $demoData['email'],
                'company' => $demoData['company'],
                'company_size' => $demoData['companySize'],
                'timeline' => $demoData['timeline'],
                'event_types' => $demoData['eventTypes'],
                'newsletter' => $demoData['newsletter'] ?? false,
            ]);

            return back()->with('success', 'Demo request submitted successfully. Our sales team will contact you within 24 hours!');

        } catch (\Exception $e) {
            Log::error('Demo request submission failed', [
                'error' => $e->getMessage(),
                'email' => $demoData['email'],
                'company' => $demoData['company'],
            ]);

            return back()->with('error', 'Failed to submit demo request. Please try again later.');
        }
    }
}
