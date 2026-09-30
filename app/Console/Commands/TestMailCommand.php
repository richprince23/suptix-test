<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test mail configuration and notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Testing mail configuration...');

            // Test basic mail sending
            \Illuminate\Support\Facades\Mail::raw('This is a test email from Suptix', function ($message) {
                $message->to('aikinsr@ymail.com')
                        ->subject('Test Email from Suptix');
            });

            $this->info('✅ Mail sent successfully!');
            
            // Test notification system
            $this->info('Testing notification system...');
            
            $paymentInfo = \App\Models\PaymentInfo::first();
            if (!$paymentInfo) {
                $this->error('No PaymentInfo records found to test with.');
                return;
            }
            
            $systemAdmin = new \App\Models\SystemAdmin();
            
            \Illuminate\Support\Facades\Notification::send($systemAdmin, new \App\Notifications\NewPaymentInfoSubmissionNotification($paymentInfo));
            
            $this->info('✅ Notification queued successfully!');
            
            // Check queue status
            $jobCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $this->info("Jobs in queue: {$jobCount}");
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
