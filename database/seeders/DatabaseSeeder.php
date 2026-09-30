<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run roles and permissions seeder first
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SystemAdminSeeder::class);
        // $this->call(OrderTestSeeder::class);

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@user.com',
        // ]);

        // User::factory()->create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@user.com',
        //     'role' => 'admin',
        // ]);
    }
}
