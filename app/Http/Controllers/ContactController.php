<?php

namespace App\Http\Controllers;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactFormMail;
use App\Models\Contact;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        SeoService::setContactUsSEO();
        return Inertia::render('ContactUs', [
            'seo' => SeoService::contactUs(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'newsletter' => 'boolean',
            'honeypot' => 'nullable|string|max:0', // Honeypot field must be empty
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $contactData = $validator->validated();

        // Check honeypot - if filled, it's likely spam
        if (! empty($contactData['honeypot'])) {
            \Log::warning('Contact form spam attempt detected', [
                'email' => $contactData['email'],
                'honeypot_value' => $contactData['honeypot'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return success to not let the bot know it was detected
            return back()->with('success', 'Your message has been sent successfully. We\'ll get back to you soon!');
        }

        // Store in database
        $contact = Contact::create([
            'first_name' => $contactData['firstName'],
            'last_name' => $contactData['lastName'],
            'email' => $contactData['email'],
            'phone' => $contactData['phone'],
            'subject' => $contactData['subject'],
            'message' => $contactData['message'],
            'newsletter' => $contactData['newsletter'] ?? false,
            'honeypot' => null, // Don't store honeypot data
        ]);

        // Remove honeypot from data before processing
        unset($contactData['honeypot']);

        try {
            // Send email to admin/support team
            Mail::to(config('mail.support_email', 'support@suptix.live'))
                ->sendNow(new ContactFormMail($contactData));

            // Confirmation email removed to reduce email costs
            // On-screen confirmation message is sufficient for user experience
            // Mail::to($contactData['email'])
            //     ->sendNow(new ContactConfirmationMail($contactData));

            // Log the contact submission
            \Log::info('Contact form submission', [
                'name' => $contactData['firstName'].' '.$contactData['lastName'],
                'email' => $contactData['email'],
                'subject' => $contactData['subject'],
                'newsletter' => $contactData['newsletter'] ?? false,
            ]);

            return back()->with('success', 'Your message has been sent successfully. We\'ll get back to you soon!');

        } catch (\Exception $e) {
            \Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'email' => $contactData['email'],
            ]);

            return back()->with('error', 'Failed to send message. Please try again later.');
        }
    }
}
