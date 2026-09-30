<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterWelcomeMail;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'honeypot' => 'nullable|string|max:0', // Honeypot field must be empty
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Check honeypot - if filled, it's likely spam
        if (!empty($data['honeypot'])) {
            \Log::warning('Newsletter subscription spam attempt detected', [
                'email' => $data['email'],
                'honeypot_value' => $data['honeypot'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return success to not let the bot know it was detected
            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed to our newsletter!'
            ]);
        }

        try {
            // Check if email already exists
            $existing = Newsletter::where('email', $data['email'])->first();

            if ($existing) {
                if ($existing->isActive()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already subscribed to our newsletter.'
                    ], 409);
                } else {
                    // Resubscribe if previously unsubscribed
                    $existing->resubscribe();
                    $newsletter = $existing;
                }
            } else {
                // Create new subscription
                $newsletter = Newsletter::create([
                    'email' => $data['email'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            // Welcome email removed to reduce email costs
            // First newsletter will serve as welcome/confirmation
            // Mail::to($newsletter->email)->sendNow(new NewsletterWelcomeMail($newsletter));

            \Log::info('Newsletter subscription', [
                'email' => $newsletter->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed to our newsletter! You\'ll receive our next update.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Newsletter subscription failed', [
                'error' => $e->getMessage(),
                'email' => $data['email'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe. Please try again later.'
            ], 500);
        }
    }

    public function unsubscribe(Request $request, $token)
    {
        $newsletter = Newsletter::where('unsubscribe_token', $token)->first();

        if (!$newsletter) {
            abort(404, 'Invalid unsubscribe link.');
        }

        if ($newsletter->isActive()) {
            $newsletter->unsubscribe();

            \Log::info('Newsletter unsubscription', [
                'email' => $newsletter->email,
                'ip' => $request->ip(),
            ]);
        }

        return view('newsletter.unsubscribed', compact('newsletter'));
    }

    public function unsubscribePage($token)
    {
        $newsletter = Newsletter::where('unsubscribe_token', $token)->first();

        if (!$newsletter) {
            abort(404, 'Invalid unsubscribe link.');
        }

        return view('newsletter.unsubscribe-confirm', compact('newsletter'));
    }
}
