<?php

namespace App\Http\Controllers;

use App\Models\PaymentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use App\Models\SystemAdmin;
use App\Notifications\NewPaymentInfoSubmissionNotification;
use Inertia\Inertia;

class PaymentInfoController extends Controller
{
    public function index()
    {
        return Inertia::render('PaymentInfoSubmission', [
            'seo' => [
                'title' => 'Submit Payment Information - Suptix',
                'description' => 'Submit your payment information to start receiving payments for your events.',
                'keywords' => 'payment information, bank account, organizer payments',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:20',
            'bank_code' => 'required|string|max:10',
            'payment_channel' => 'required|in:bank,mobile_money',
            'organizer_id' => 'required|exists:organizers,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        $data = $validator->validated();

        // First verify the account with Paystack
        $verificationResult = $this->verifyAccountWithPaystack($data['account_number'], $data['bank_code']);

        if (! $verificationResult['success']) {
            return back()->withErrors(['account_number' => $verificationResult['message']]);
        }

        // Check if organizer already has pending/approved payment info
        $existingPaymentInfo = PaymentInfo::where('organizer_id', $data['organizer_id'])
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('status', 'approved');
            })
            ->first();

        if ($existingPaymentInfo) {
            return back()->withErrors(['general' => 'You already have a payment information submission. Please wait for approval or contact support.']);
        }

        try {
            // Create payment info record
            $paymentInfo = PaymentInfo::create([
                'organizer_id' => $data['organizer_id'],
                'submitted_by' => auth()->id(),
                'business_name' => $data['business_name'],
                'account_number' => $data['account_number'],
                'account_name' => $verificationResult['account_name'],
                'bank_code' => $data['bank_code'],
                'payment_channel' => $data['payment_channel'],
                'status' => 'pending',
                'submitted_at' => now(),
                'paystack_verified' => true,
                'verification_response' => $verificationResult['response'],
            ]);

            // Send notifications to admin
            $this->notifyAdminOfNewSubmission($paymentInfo);

            return back()->with('success', 'Payment information submitted successfully! You will be notified once it has been reviewed and approved.');

        } catch (\Exception $e) {
            Log::error('Payment info submission failed', [
                'error' => $e->getMessage(),
                'organizer_id' => $data['organizer_id'],
            ]);

            return back()->with('error', 'Failed to submit payment information. Please try again later.');
        }
    }

    private function verifyAccountWithPaystack($accountNumber, $bankCode)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.config('services.paystack.secret_key'),
                'Content-Type' => 'application/json',
            ])->get('https://api.paystack.co/bank/resolve', [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status']) {
                    return [
                        'success' => true,
                        'account_name' => $data['data']['account_name'],
                        'response' => $data,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Invalid account number or bank code. Please verify your details.',
            ];

        } catch (\Exception $e) {
            Log::error('Paystack account verification failed', [
                'error' => $e->getMessage(),
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to verify account at this time. Please try again later.',
            ];
        }
    }

    private function notifyAdminOfNewSubmission(PaymentInfo $paymentInfo)
    {
        try {
            // Use Laravel's notification system to send both email and SMS
            $systemAdmin = new SystemAdmin();
            
            Notification::send($systemAdmin, new NewPaymentInfoSubmissionNotification($paymentInfo));

            // Mark as notified
            $paymentInfo->update([
                'admin_notified' => true,
                'admin_notified_at' => now(),
            ]);

            Log::info('Admin notification sent for payment info submission', [
                'payment_info_id' => $paymentInfo->id,
                'organizer_id' => $paymentInfo->organizer_id,
                'business_name' => $paymentInfo->business_name,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to notify admin of payment info submission', [
                'error' => $e->getMessage(),
                'payment_info_id' => $paymentInfo->id,
            ]);
        }
    }

    public function getBanks()
    {
        $banks = [
            '190100' => 'Stanbic Bank Ghana',
            '030100' => 'Absa Bank Ghana',
            '280100' => 'Access Bank Ghana',
            '080100' => 'ADB Bank Limited',
            '070101' => 'ARB Apex Bank',
            '140100' => 'CAL Bank',
            '340100' => 'Consolidated Bank Ghana Limited',
            '130100' => 'Ecobank Ghana',
            '060100' => 'UBA Ghana',
            '240100' => 'Fidelity Bank Ghana',
            '040100' => 'GCB Bank Limited',
            '180100' => 'Prudential Bank',
            '110100' => 'Republic Bank Ghana',
            '090100' => 'Societe Generale Ghana',
            '020100' => 'Standard Chartered Bank Ghana',
            '100100' => 'Universal Merchant Bank',
            '120100' => 'Zenith Bank Ghana',
            '010100' => 'Bank of Ghana',
            '230100' => 'GT Bank Ghana',
            'MTN' => 'MTN Mobile Money',
            'VOD' => 'Vodafone Cash',
            'ATL' => 'AirtelTigo Money',
            'ZEEPAY' => 'Zeepay',
        ];

        return response()->json(['banks' => $banks]);
    }
}
