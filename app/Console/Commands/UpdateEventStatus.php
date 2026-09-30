<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class UpdateEventStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-statuses {--dry-run : Show what would be updated without making changes}
    {--status= : Comma-separated list of statuses to check (default: published,live,past)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event statuses based on current time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updatedCount = 0;
        $dryRun = $this->option('dry-run');

        $statuses = explode(',', $this->option('status') ?? 'published,live,past');
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->line('');
        }

        // Only check events that might need updating (including past events that might revert to live)
        Event::whereIn('status', ['published', 'live', 'past'])
            ->chunkById(100, function ($events) use (&$updatedCount, $dryRun) {
                foreach ($events as $event) {
                    $currentStatus = $event->status;
                    $newStatus = $event->calculateCurrentStatus();

                    if ($currentStatus !== $newStatus) {
                        $updatedCount++;

                        if ($dryRun) {
                            $this->line("Would update event: {$event->title} from {$currentStatus} to {$newStatus}");
                        } else {
                            if ($event->updateStatusIfNeeded()) {
                                $this->info("Updated event: {$event->title} to {$event->status}");
                            }
                        }
                    }
                }
            });

        if ($dryRun) {
            $this->line('');
            $this->info("Would update {$updatedCount} events");
        } else {
            $this->info("Updated {$updatedCount} events");
        }
    }
}
