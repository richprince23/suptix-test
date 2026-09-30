<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a system administrator account
        $systemAdmin = User::firstOrCreate(
            ['email' => 'admin@suptix.live'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@suptix.live',
                'password' => Hash::make('admin123'),
                'is_system_admin' => true,
                'force_renew_password' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($systemAdmin->wasRecentlyCreated) {
            $this->command->info('System administrator created successfully!');
            $this->command->info('Email: admin@suptix.live');
            $this->command->info('Password: admin123');
            $this->command->warn('Please change the default password after first login.');
        } else {
            $this->command->info('System administrator already exists.');
        }

        // Create a second system admin for testing
        // $testAdmin = User::firstOrCreate(
        //     ['email' => 'test-admin@suptix.live'],
        //     [
        //         'name' => 'Test System Admin',
        //         'email' => 'test-admin@suptix.live',
        //         'password' => Hash::make('testadmin123'),
        //         'is_system_admin' => true,
        //         'force_renew_password' => true,
        //         'email_verified_at' => now(),
        //     ]
        // );

        // if ($testAdmin->wasRecentlyCreated) {
        //     $this->command->info('Test system administrator created successfully!');
        //     $this->command->info('Email: test-admin@suptix.live');
        //     $this->command->info('Password: testadmin123 (will be required to change on first login)');
        // }
    }
}
