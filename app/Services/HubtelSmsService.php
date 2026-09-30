<?php

namespace App\Services;

use App\Contracts\SmsProviderInterface;
use App\Helpers\PhoneFormatter;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubtelSmsService implements SmsProviderInterface
{
    private string $baseUrl;

    private string $clientId;

    private string $clientSecret;

    private string $senderId;

    public function __construct()
    {
        $this->baseUrl = config('services.hubtel.base_url', 'https://sms.hubtel.com');
        $this->clientId = config('services.hubtel.client_id') ?? '';
        $this->clientSecret = config('services.hubtel.client_secret') ?? '';
        $this->senderId = config('services.hubtel.sender_id', 'Suptix');

        if (! $this->clientId || ! $this->clientSecret) {
            Log::warning('Hubtel SMS credentials not configured');
        }
    }

    public function getProviderName(): string
    {
        return 'hubtel';
    }

    public function sendSms(string $phoneNumber, string $message): array
    {
        try {
            $formattedPhone = PhoneFormatter::toE164Ghana($phoneNumber);

            if (! $formattedPhone) {
                throw new \Exception('Invalid phone number format');
            }

            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->baseUrl.'/v1/messages/send', [
                    'From' => $this->senderId,
                    'To' => $formattedPhone,
                    'Content' => $message,
                    'Type' => 0,
                    'RegisteredDelivery' => true,
                ]);

            $response->throw();

            $responseData = $response->json();

            Log::info('Hubtel SMS sent successfully', [
                'phone' => $formattedPhone,
                'response' => $responseData,
            ]);

            return [
                'success' => true,
                'provider' => $this->getProviderName(),
                'message_id' => $responseData['messageId'] ?? $responseData['MessageId'] ?? null,
                'status' => $responseData['status'] ?? $responseData['Status'] ?? null,
                'response' => $responseData,
            ];

        } catch (RequestException $e) {
            Log::error('Hubtel SMS request failed', [
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
            Log::error('Hubtel SMS service error', [
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
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    public function getBalance(): array
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->get($this->baseUrl.'/v1/account/balance');

            $response->throw();

            return [
                'success' => true,
                'balance' => $response->json(),
            ];

        } catch (RequestException $e) {
            Log::error('Failed to get Hubtel balance', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
