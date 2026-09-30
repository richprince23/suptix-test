<?php

namespace App\Console\Commands;

use App\Mail\RsvpConfirmationMail;
use App\Models\Event;
use App\Models\RSVP;
use App\Services\EventCreditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DiagnoseMailCommand extends Command
{
    protected $signature = 'mail:diagnose {email? : Address to send the real-mailable test to}';

    protected $description = 'Diagnose why application mails fail while raw test mail works';

    public function handle(EventCreditService $creditService): int
    {
        $this->section('1. Effective mail config (as PHP-FPM/CLI sees it)');
        $this->line('config cached: ' . (app()->configurationIsCached() ? 'yes' : 'no'));
        $this->line('mailer:        ' . config('mail.default'));
        $this->line('host:          ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->line('username:      ' . (config('mail.mailers.smtp.username') ? 'set' : 'MISSING'));
        $this->line('password:      ' . (config('mail.mailers.smtp.password') ? 'set' : 'MISSING'));
        $this->line('from:          ' . config('mail.from.address'));
        $this->line('queue conn:    ' . config('queue.default'));

        $this->section('2. Queue health');
        try {
            $jobs = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            $oldest = DB::table('jobs')->min('created_at');
            $this->line("pending jobs:  {$jobs}" . ($oldest ? ' (oldest queued at ' . date('Y-m-d H:i:s', (int) $oldest) . ')' : ''));
            $this->line("failed jobs:   {$failed}");
            if ($jobs > 0 && $oldest && (time() - (int) $oldest) > 300) {
                $this->error('Jobs older than 5 minutes are still pending — queue worker is NOT consuming. Check supervisord queue program / /var/log/queue.log');
            }
            if ($failed > 0) {
                $recent = DB::table('failed_jobs')->orderByDesc('id')->first();
                $this->warn('Most recent failure: ' . substr($recent->exception ?? '', 0, 500));
            }
        } catch (\Throwable $e) {
            $this->error('Queue tables unreadable: ' . $e->getMessage());
        }

        $this->section('3. Credit gates (blocks RSVP confirmation mails silently)');
        try {
            $settings = \App\Models\RsvpCreditSettings::current();
            $this->line('brevo_daily_limit: ' . $settings->brevo_daily_limit);
            $today = now()->toDateString();
            $dailyTotal = \App\Models\EventCreditAccount::where('global_daily_email_date', $today)
                ->sum('global_daily_email_count');
            $this->line("emails sent today (counter): {$dailyTotal}");
            if ($settings->brevo_daily_limit <= 0) {
                $this->error('brevo_daily_limit <= 0 — canSendConfirmationEmail() blocks EVERY mail.');
            } elseif ($dailyTotal >= $settings->brevo_daily_limit) {
                $this->error('Daily quota reached — confirmation mails are being skipped.');
            }
            $event = Event::latest()->first();
            if ($event) {
                $check = $creditService->canSendConfirmationEmail($event);
                $this->line("latest event #{$event->id} gate: " . ($check->allowed ? 'ALLOWED' : 'BLOCKED - ' . $check->reason));
            }
        } catch (\Throwable $e) {
            $this->error('Credit gate check failed: ' . $e->getMessage());
        }

        $this->section('4. Recent send failures in laravel.log');
        $log = storage_path('logs/laravel.log');
        if (is_readable($log)) {
            $lines = array_filter(
                array_slice(file($log) ?: [], -2000),
                fn ($l) => str_contains($l, 'Failed to send') || str_contains($l, 'email skipped')
            );
            $lines = array_slice($lines, -10);
            $lines
                ? $this->line(implode('', $lines))
                : $this->line('No "Failed to send" / "email skipped" entries in last 2000 lines.');
        } else {
            $this->warn("Cannot read {$log}");
        }

        $this->section('5. Real mailable send (same path as app, not Mail::raw)');
        $email = $this->argument('email');
        if (! $email) {
            $this->line('Pass an email address to run this step: php artisan mail:diagnose you@example.com');

            return self::SUCCESS;
        }
        $rsvp = RSVP::latest()->first();
        $event = $rsvp?->event()->with('organizer')->first();
        if (! $rsvp || ! $event) {
            $this->warn('No RSVP/Event found to build RsvpConfirmationMail with.');

            return self::SUCCESS;
        }
        try {
            Mail::to($email)->sendNow(new RsvpConfirmationMail($rsvp, $event));
            $this->info('Real mailable sent OK — transport and rendering both work; problem is in the gate/queue findings above.');
        } catch (\Throwable $e) {
            $this->error('Real mailable FAILED (this is your production bug):');
            $this->error(get_class($e) . ': ' . $e->getMessage());
            $this->line($e->getTraceAsString());
        }

        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->info($title);
        $this->line(str_repeat('-', strlen($title)));
    }
}
