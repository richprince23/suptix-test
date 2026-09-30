<?php

namespace App\Console\Commands;

use App\Services\HubtelSmsService;
use Illuminate\Console\Command;

class TestSmsCommand extends Command
{
    protected $signature = 'sms:test {phone} {message?}';

    protected $description = 'Test SMS functionality using Hubtel';

    public function handle(HubtelSmsService $smsService): int
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message') ?? 'Test message from Suptix! 🎫';

        if (! $smsService->isConfigured()) {
            $this->error('Hubtel SMS is not configured. Please set HUBTEL_CLIENT_ID and HUBTEL_CLIENT_SECRET in your .env file.');

            return 1;
        }

        $this->info("Testing SMS to {$phone}...");

        $result = $smsService->sendSms($phone, $message);

        if ($result['success']) {
            $this->info('SMS sent successfully!');
            $this->line('Message ID: '.($result['message_id'] ?? 'N/A'));
            $this->line('Status: '.($result['status'] ?? 'N/A'));
        } else {
            $this->error('SMS failed to send.');
            $this->line('Error: '.($result['error'] ?? 'Unknown error'));
        }

        return $result['success'] ? 0 : 1;
    }
}
