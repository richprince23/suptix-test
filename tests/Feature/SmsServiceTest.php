<?php

namespace Tests\Feature;

use App\Helpers\PhoneFormatter;
use App\Services\GiantSmsService;
use App\Services\HubtelSmsService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    use DatabaseTransactions;

    // ---------------------------------------------------------------------------
    // PhoneFormatter
    // ---------------------------------------------------------------------------

    public function test_formats_local_ten_digit_ghana_number(): void
    {
        $this->assertSame('+233501234567', PhoneFormatter::toE164Ghana('0501234567'));
    }

    public function test_formats_nine_digit_ghana_number(): void
    {
        $this->assertSame('+233501234567', PhoneFormatter::toE164Ghana('501234567'));
    }

    public function test_formats_twelve_digit_ghana_number_without_plus(): void
    {
        $this->assertSame('+233501234567', PhoneFormatter::toE164Ghana('233501234567'));
    }

    public function test_returns_already_formatted_e164_number(): void
    {
        $this->assertSame('+233501234567', PhoneFormatter::toE164Ghana('+233501234567'));
    }

    public function test_returns_null_for_invalid_number(): void
    {
        $this->assertNull(PhoneFormatter::toE164Ghana('12345'));
        $this->assertNull(PhoneFormatter::toE164Ghana('abcdefghij'));
    }

    public function test_strips_non_numeric_characters_before_formatting(): void
    {
        $this->assertSame('+233501234567', PhoneFormatter::toE164Ghana('050-123-4567'));
        $this->assertSame('+233501234567', PhoneFormatter::toE164Ghana('(050) 123 4567'));
    }

    // ---------------------------------------------------------------------------
    // HubtelSmsService
    // ---------------------------------------------------------------------------

    public function test_hubtel_sends_sms_successfully(): void
    {
        config([
            'services.hubtel.client_id' => 'test-id',
            'services.hubtel.client_secret' => 'test-secret',
            'services.hubtel.sender_id' => 'Suptix',
        ]);

        Http::fake([
            'sms.hubtel.com/*' => Http::response([
                'messageId' => 'HUBTEL-MSG-001',
                'status' => 0,
            ], 200),
        ]);

        $service = $this->app->make(HubtelSmsService::class);
        $result = $service->sendSms('0501234567', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertSame('hubtel', $result['provider']);
    }

    public function test_hubtel_returns_failure_on_invalid_phone(): void
    {
        config([
            'services.hubtel.client_id' => 'test-id',
            'services.hubtel.client_secret' => 'test-secret',
        ]);

        $service = $this->app->make(HubtelSmsService::class);
        $result = $service->sendSms('00000', 'Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid phone number', $result['error']);
    }

    public function test_hubtel_is_configured_when_credentials_present(): void
    {
        config([
            'services.hubtel.client_id' => 'test-id',
            'services.hubtel.client_secret' => 'test-secret',
        ]);

        $this->assertTrue($this->app->make(HubtelSmsService::class)->isConfigured());
    }

    public function test_hubtel_is_not_configured_when_credentials_missing(): void
    {
        config(['services.hubtel.client_id' => null, 'services.hubtel.client_secret' => null]);
        $this->app->forgetInstance(HubtelSmsService::class);

        $this->assertFalse($this->app->make(HubtelSmsService::class)->isConfigured());
    }

    // ---------------------------------------------------------------------------
    // GiantSmsService
    // ---------------------------------------------------------------------------

    public function test_giant_sms_sends_sms_successfully(): void
    {
        config([
            'services.giant_sms.username' => 'test-user',
            'services.giant_sms.password' => 'test-pass',
            'services.giant_sms.sender_id' => 'Suptix',
        ]);

        Http::fake([
            'api.giantsms.com/*' => Http::response([
                'status' => true,
                'message' => 'Message sent',
                'data' => [
                    'message_id' => 'GIANT-MSG-001',
                    'status' => 'Sent',
                ],
            ], 200),
        ]);

        $service = $this->app->make(GiantSmsService::class);
        $result = $service->sendSms('0501234567', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertSame('giant_sms', $result['provider']);
        $this->assertSame('GIANT-MSG-001', $result['message_id']);
    }

    public function test_giant_sms_returns_failure_when_api_returns_false_status(): void
    {
        config([
            'services.giant_sms.username' => 'test-user',
            'services.giant_sms.password' => 'test-pass',
        ]);

        Http::fake([
            'api.giantsms.com/*' => Http::response([
                'status' => false,
                'message' => 'Insufficient balance',
            ], 200),
        ]);

        $service = $this->app->make(GiantSmsService::class);
        $result = $service->sendSms('0501234567', 'Test message');

        $this->assertFalse($result['success']);
        $this->assertSame('giant_sms', $result['provider']);
    }

    public function test_giant_sms_returns_failure_on_invalid_phone(): void
    {
        config([
            'services.giant_sms.username' => 'test-user',
            'services.giant_sms.password' => 'test-pass',
        ]);

        $service = $this->app->make(GiantSmsService::class);
        $result = $service->sendSms('00000', 'Test message');

        $this->assertFalse($result['success']);
    }

    public function test_giant_sms_is_configured_when_credentials_present(): void
    {
        config([
            'services.giant_sms.username' => 'test-user',
            'services.giant_sms.password' => 'test-pass',
        ]);

        $this->assertTrue($this->app->make(GiantSmsService::class)->isConfigured());
    }

    public function test_giant_sms_is_not_configured_when_credentials_missing(): void
    {
        config(['services.giant_sms.username' => null, 'services.giant_sms.password' => null]);

        $this->assertFalse($this->app->make(GiantSmsService::class)->isConfigured());
    }

    // ---------------------------------------------------------------------------
    // SmsService (fallback chain)
    // ---------------------------------------------------------------------------

    public function test_sms_service_uses_primary_provider_when_it_succeeds(): void
    {
        config([
            'services.sms.primary' => 'hubtel',
            'services.hubtel.client_id' => 'test-id',
            'services.hubtel.client_secret' => 'test-secret',
        ]);

        $this->app->forgetInstance(HubtelSmsService::class);

        Http::fake([
            'sms.hubtel.com/*' => Http::response([
                'messageId' => 'HUBTEL-MSG-001',
                'status' => 0,
            ], 200),
        ]);

        $result = $this->app->make(SmsService::class)->sendSms('0501234567', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertSame('hubtel', $result['provider']);
    }

    public function test_sms_service_falls_back_to_giant_sms_when_hubtel_fails(): void
    {
        config([
            'services.sms.primary' => 'hubtel',
            'services.hubtel.client_id' => 'test-id',
            'services.hubtel.client_secret' => 'test-secret',
            'services.giant_sms.username' => 'test-user',
            'services.giant_sms.password' => 'test-pass',
        ]);

        Http::fake([
            'sms.hubtel.com/*' => Http::response(['error' => 'Service unavailable'], 503),
            'api.giantsms.com/*' => Http::response([
                'status' => true,
                'message' => 'Message sent',
                'data' => ['message_id' => 'GIANT-MSG-001', 'status' => 'Sent'],
            ], 200),
        ]);

        $result = $this->app->make(SmsService::class)->sendSms('0501234567', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertSame('giant_sms', $result['provider']);
    }

    public function test_sms_service_skips_unconfigured_primary_and_uses_fallback(): void
    {
        config([
            'services.sms.primary' => 'hubtel',
            'services.hubtel.client_id' => null,
            'services.hubtel.client_secret' => null,
            'services.giant_sms.username' => 'test-user',
            'services.giant_sms.password' => 'test-pass',
        ]);

        $this->app->forgetInstance(HubtelSmsService::class);

        Http::fake([
            'api.giantsms.com/*' => Http::response([
                'status' => true,
                'message' => 'Message sent',
                'data' => ['message_id' => 'GIANT-MSG-002', 'status' => 'Sent'],
            ], 200),
        ]);

        $result = $this->app->make(SmsService::class)->sendSms('0501234567', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertSame('giant_sms', $result['provider']);
    }

    public function test_sms_service_returns_failure_when_all_providers_fail(): void
    {
        config([
            'services.hubtel.client_id' => 'test-id',
            'services.hubtel.client_secret' => 'test-secret',
            'services.giant_sms.username' => 'test-user',
            'services.giant_sms.password' => 'test-pass',
        ]);

        $this->app->forgetInstance(HubtelSmsService::class);

        Http::fake([
            'sms.hubtel.com/*' => Http::response([], 503),
            'api.giantsms.com/*' => Http::response(['status' => false, 'message' => 'Error'], 200),
        ]);

        Log::spy();

        $result = $this->app->make(SmsService::class)->sendSms('0501234567', 'Test message');

        $this->assertFalse($result['success']);
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    public function test_sms_service_returns_failure_when_no_providers_configured(): void
    {
        config([
            'services.hubtel.client_id' => null,
            'services.hubtel.client_secret' => null,
            'services.giant_sms.username' => null,
            'services.giant_sms.password' => null,
        ]);

        $this->app->forgetInstance(HubtelSmsService::class);

        $result = $this->app->make(SmsService::class)->sendSms('0501234567', 'Test message');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No SMS provider', $result['error']);
    }
}
