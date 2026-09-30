<?php

namespace App\Services;

use App\Contracts\SmsProviderInterface;
use App\Helpers\PhoneFormatter;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GiantSmsService implements SmsProviderInterface
{
    private string $baseUrl;

    private string $username;

    private string $password;

    private string $senderId;

    public function __construct()
    {
        $this->baseUrl = config('services.giant_sms.base_url', 'https://api.giantsms.com/api/v1');
        $this->username = config('services.giant_sms.username') ?? '';
        $this->password = config('services.giant_sms.password') ?? '';
        $this->senderId = config('services.giant_sms.sender_id', 'Suptix');

        if (! $this->username || ! $this->password) {
            Log::warning('GiantSMS credentials not configured');
        }
    }

    public function getProviderName(): string
    {
        return 'giant_sms';
    }

    public function sendSms(string $phoneNumber, string $message): array
    {
        try {
            $formattedPhone = PhoneFormatter::toE164Ghana($phoneNumber);

            if (! $formattedPhone) {
                throw new \Exception('Invalid phone number format');
            }

            $response = Http::get($this->baseUrl.'/send', [
                'username' => $this->username,
                'password' => $this->password,
                'from' => $this->senderId,
                'to' => $formattedPhone,
                'msg' => $message,
            ]);

            $response->throw();

            $responseData = $response->json();

            if (! ($responseData['status'] ?? false)) {
                throw new \Exception($responseData['message'] ?? 'GiantSMS returned a failure status');
            }

            Log::info('GiantSMS SMS sent successfully', [
                'phone' => $formattedPhone,
                'response' => $responseData,
            ]);

            return [
                'success' => true,
                'provider' => $this->getProviderName(),
                'message_id' => $responseData['data']['message_id'] ?? null,
                'status' => $responseData['data']['status'] ?? null,
                'response' => $responseData,
            ];

        } catch (RequestException $e) {
            Log::error('GiantSMS request failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'response' => $e->response->json(),
            ]);

            return [
                'success' => false,
                'provider' => $this->getProviderName(),
                'error' => $e->getMessage(),
                'response' => $e->response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('GiantSMS service error', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'provider' => $this->getProviderName(),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function isConfigured(): bool
    {
        return ! empty($this->username) && ! empty($this->password);
    }

    public function getBalance(): array
    {
        try {
            $response = Http::get($this->baseUrl.'/balance', [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            $response->throw();

            $responseData = $response->json();

            return [
                'success' => true,
                'balance' => $responseData['message'] ?? null,
            ];

        } catch (RequestException $e) {
            Log::error('Failed to get GiantSMS balance', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
