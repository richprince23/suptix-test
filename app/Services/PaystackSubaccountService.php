<?php

namespace App\Services;

use App\Models\PaymentInfo;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackSubaccountService
{
    private string $paystackSecretKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->paystackSecretKey = config('services.paystack.secret_key') ?? 'test_secret_key';
        $this->baseUrl = 'https://api.paystack.co';
    }

    /**
     * Update a subaccount on Paystack
     */
    public function updateSubaccount(PaymentInfo $paymentInfo): array
    {
        if (! $paymentInfo->paystack_subaccount_code) {
            throw new \InvalidArgumentException('PaymentInfo must have a Paystack subaccount code to update');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->put("{$this->baseUrl}/subaccount/{$paymentInfo->paystack_subaccount_code}", [
                'business_name' => $paymentInfo->business_name,
                'settlement_bank' => $paymentInfo->bank_code,
                'account_number' => $paymentInfo->account_number,
                'percentage_charge' => $paymentInfo->percentage_charge,
                'description' => "Subaccount for {$paymentInfo->business_name}",
                'primary_contact_email' => $paymentInfo->organizer->email ?? null,
                'primary_contact_name' => $paymentInfo->organizer->name ?? null,
                'primary_contact_phone' => $paymentInfo->organizer->phone ?? null,
                'metadata' => array_merge($paymentInfo->metadata ?? [], [
                    'updated_at' => now()->toISOString(),
                    'updated_via' => 'dashboard',
                    'organizer_id' => $paymentInfo->organizer_id,
                ]),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Paystack subaccount updated successfully', [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                    'response' => $responseData,
                ]);

                // Update the PaymentInfo with the latest response data
                $this->updatePaymentInfoFromPaystackResponse($paymentInfo, $responseData['data']);

                return $responseData;
            } else {
                $errorData = $response->json();
                Log::error('Failed to update Paystack subaccount', [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                    'status' => $response->status(),
                    'error' => $errorData,
                ]);

                throw new RequestException($response ?? 'Failed to update subaccount: '.($errorData['message'] ?? 'Unknown error'));
            }
        } catch (RequestException $e) {
            Log::error('Paystack API request failed during subaccount update', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during subaccount update', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Fetch subaccount details from Paystack
     */
    public function fetchSubaccount(PaymentInfo $paymentInfo): array
    {
        if (! $paymentInfo->paystack_subaccount_code) {
            throw new \InvalidArgumentException('PaymentInfo must have a Paystack subaccount code to fetch');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/subaccount/{$paymentInfo->paystack_subaccount_code}");

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Paystack subaccount fetched successfully', [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                ]);

                return $responseData;
            } else {
                $errorData = $response->json();
                Log::error('Failed to fetch Paystack subaccount', [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                    'status' => $response->status(),
                    'error' => $errorData,
                ]);

                throw new RequestException($response ?? 'Failed to fetch subaccount: '.($errorData['message'] ?? 'Unknown error'));
            }
        } catch (RequestException $e) {
            Log::error('Paystack API request failed during subaccount fetch', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during subaccount fetch', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update PaymentInfo model with data from Paystack response
     */
    private function updatePaymentInfoFromPaystackResponse(PaymentInfo $paymentInfo, array $subaccountData): void
    {
        $paymentInfo->updateQuietly([
            'paystack_response' => $subaccountData,
            'settlement_schedule' => $subaccountData['settlement_schedule'] ?? $paymentInfo->settlement_schedule,
            'is_active' => $subaccountData['active'] ?? $paymentInfo->is_active,
            'metadata' => array_merge($paymentInfo->metadata ?? [], $subaccountData['metadata'] ?? []),
        ]);

        Log::info('PaymentInfo updated with Paystack response data', [
            'payment_info_id' => $paymentInfo->id,
            'subaccount_code' => $paymentInfo->paystack_subaccount_code,
        ]);
    }

    /**
     * Test if the Paystack API is accessible
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->paystackSecretKey,
            ])->get("{$this->baseUrl}/bank");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Paystack connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a subaccount from Paystack
     */
    public function deleteSubaccount(PaymentInfo $paymentInfo): array
    {
        if (! $paymentInfo->paystack_subaccount_code) {
            throw new \InvalidArgumentException('PaymentInfo must have a Paystack subaccount code to delete');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->delete("{$this->baseUrl}/subaccount/{$paymentInfo->paystack_subaccount_code}");

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Paystack subaccount deleted successfully', [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                    'response' => $responseData,
                ]);

                return $responseData;
            } else {
                $errorData = $response->json();
                Log::error('Failed to delete Paystack subaccount', [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                    'status' => $response->status(),
                    'error' => $errorData,
                ]);

                throw new RequestException($response ?? 'Failed to delete subaccount: '.($errorData['message'] ?? 'Unknown error'));
            }
        } catch (RequestException $e) {
            Log::error('Paystack API request failed during subaccount deletion', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during subaccount deletion', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * List all subaccounts from Paystack
     */
    public function listAllSubaccounts(int $perPage = 50, int $page = 1): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/subaccount", [
                'perPage' => $perPage,
                'page' => $page,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Paystack subaccounts fetched successfully', [
                    'total' => $responseData['meta']['total'] ?? 0,
                    'page' => $page,
                    'per_page' => $perPage,
                ]);

                return $responseData;
            } else {
                $errorData = $response->json();
                Log::error('Failed to fetch Paystack subaccounts', [
                    'status' => $response->status(),
                    'error' => $errorData,
                ]);

                throw new RequestException($response ?? 'Failed to fetch subaccounts: '.($errorData['message'] ?? 'Unknown error'));
            }
        } catch (RequestException $e) {
            Log::error('Paystack API request failed during subaccounts list', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error during subaccounts list', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get validation messages for different error scenarios
     */
    public function getValidationMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'not found')) {
            return 'The subaccount was not found on Paystack. Please contact support.';
        }

        if (str_contains($message, 'unauthorized') || str_contains($message, 'authentication')) {
            return 'Unable to authenticate with Paystack. Please check your API configuration.';
        }

        if (str_contains($message, 'validation') || str_contains($message, 'invalid')) {
            return 'The provided information is invalid. Please check your inputs and try again.';
        }

        if (str_contains($message, 'network') || str_contains($message, 'timeout')) {
            return 'Unable to connect to Paystack. Please try again later.';
        }

        return 'An error occurred while updating the payment information. Please try again or contact support.';
    }
}
