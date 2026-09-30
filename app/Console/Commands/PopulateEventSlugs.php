<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class PopulateEventSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:populate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate slug field for existing events without slugs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating event slugs...');

        $events = Event::whereNull('slug')->orWhere('slug', '')->get();

        if ($events->isEmpty()) {
            $this->info('No events found without slugs.');

            return;
        }

        $updated = 0;

        foreach ($events as $event) {
            $slug = $event->generateUniqueSlug();
            $event->update(['slug' => $slug]);
            $updated++;

            $this->line("Updated event '{$event->title}' with slug: {$slug}");
        }

        $this->info("Successfully updated {$updated} events with slugs.");
    }
}
