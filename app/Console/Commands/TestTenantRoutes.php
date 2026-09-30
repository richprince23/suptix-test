<?php

namespace App\Console\Commands;

use App\Models\Organizer;
use Filament\Facades\Filament;
use Illuminate\Console\Command;

class TestTenantRoutes extends Command
{
    protected $signature = 'test:tenant-routes';

    protected $description = 'Test that tenant-aware routes are generated correctly';

    public function handle()
    {
        $this->info('🔗 Testing Tenant Route Generation...');
        $this->newLine();

        try {
            // Get or create a test organizer
            $organizer = Organizer::first();

            if (! $organizer) {
                $this->warn('⚠️  No organizers found in database');
                $this->line('Creating a test organizer...');

                $organizer = Organizer::create([
                    'name' => 'Test Route Organizer',
                    'email' => 'test-routes@example.com',
                    'phone' => '+1234567890',
                    'website' => 'https://test-routes.com',
                    'description' => 'Test organizer for route testing',
                ]);

                $this->info("✅ Created test organizer: {$organizer->name} (ID: {$organizer->id})");
            } else {
                $this->info("✅ Using existing organizer: {$organizer->name} (ID: {$organizer->id})");
            }

            $this->newLine();
            $this->line('🔗 <comment>Testing route generation...</comment>');

            // Test PaymentInfoResource routes
            try {
                $indexUrl = \App\Filament\Resources\PaymentInfoResource::getUrl('index', tenant: $organizer);
                $this->info("✅ Index URL: {$indexUrl}");
            } catch (\Exception $e) {
                $this->error("❌ Index URL failed: {$e->getMessage()}");
            }

            try {
                $createUrl = \App\Filament\Resources\PaymentInfoResource::getUrl('create', tenant: $organizer);
                $this->info("✅ Create URL: {$createUrl}");
            } catch (\Exception $e) {
                $this->error("❌ Create URL failed: {$e->getMessage()}");
            }

            // Test what happens when we simulate being in a tenant context
            $this->newLine();
            $this->line('🏢 <comment>Testing within tenant context...</comment>');

            // Temporarily set the tenant context
            Filament::setTenant($organizer);

            try {
                $contextCreateUrl = \App\Filament\Resources\PaymentInfoResource::getUrl('create');
                $this->info("✅ Context Create URL: {$contextCreateUrl}");
            } catch (\Exception $e) {
                $this->error("❌ Context Create URL failed: {$e->getMessage()}");
            }

            try {
                $contextIndexUrl = \App\Filament\Resources\PaymentInfoResource::getUrl('index');
                $this->info("✅ Context Index URL: {$contextIndexUrl}");
            } catch (\Exception $e) {
                $this->error("❌ Context Index URL failed: {$e->getMessage()}");
            }

            // Test route parameters
            $this->newLine();
            $this->line('📋 <comment>Route analysis:</comment>');

            $this->table(
                ['Route Type', 'URL', 'Has Tenant?'],
                [
                    ['Index (with tenant)', $indexUrl ?? 'Failed', str_contains($indexUrl ?? '', (string) $organizer->id) ? '✅ Yes' : '❌ No'],
                    ['Create (with tenant)', $createUrl ?? 'Failed', str_contains($createUrl ?? '', (string) $organizer->id) ? '✅ Yes' : '❌ No'],
                    ['Context Create', $contextCreateUrl ?? 'Failed', str_contains($contextCreateUrl ?? '', (string) $organizer->id) ? '✅ Yes' : '❌ No'],
                ]
            );

            $this->newLine();
            $this->info('💡 <comment>Widget URL recommendations:</comment>');
            $this->line('For widgets within tenant context, use:');
            $this->line('   \\App\\Filament\\Resources\\PaymentInfoResource::getUrl(\'create\')');
            $this->line('');
            $this->line('For widgets that need explicit tenant, use:');
            $this->line('   \\App\\Filament\\Resources\\PaymentInfoResource::getUrl(\'create\', tenant: Filament::getTenant())');

        } catch (\Exception $e) {
            $this->error('❌ Test failed: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
