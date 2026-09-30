<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /** @var SmsProviderInterface[] */
    private array $providers;

    public function __construct(HubtelSmsService $hubtel, GiantSmsService $giantSms)
    {
        // Order determines priority; first configured provider is tried first.
        $primary = config('services.sms.primary', 'hubtel');

        $all = [
            'hubtel' => $hubtel,
            'giant_sms' => $giantSms,
        ];

        // Put the preferred primary first, then the rest as fallbacks.
        $this->providers = $primary !== 'hubtel'
            ? array_merge(
                array_filter($all, fn ($k) => $k === $primary, ARRAY_FILTER_USE_KEY),
                array_filter($all, fn ($k) => $k !== $primary, ARRAY_FILTER_USE_KEY),
            )
            : $all;
    }

    public function sendSms(string $phoneNumber, string $message): array
    {
        $lastResult = ['success' => false, 'error' => 'No SMS provider is configured'];

        foreach ($this->providers as $provider) {
            if (! $provider->isConfigured()) {
                Log::debug('Skipping unconfigured SMS provider', ['provider' => $provider->getProviderName()]);

                continue;
            }

            $result = $provider->sendSms($phoneNumber, $message);

            if ($result['success']) {
                return $result;
            }

            Log::warning('SMS provider failed, trying next fallback', [
                'provider' => $provider->getProviderName(),
                'error' => $result['error'] ?? 'unknown',
            ]);

            $lastResult = $result;
        }

        Log::error('All SMS providers failed', ['phone' => $phoneNumber]);

        return $lastResult;
    }
}
