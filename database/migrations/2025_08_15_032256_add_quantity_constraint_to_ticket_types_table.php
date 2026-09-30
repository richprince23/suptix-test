<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            // Add check constraint to prevent negative quantities
            // Use database-specific syntax
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE ticket_types ADD CONSTRAINT check_quantity_positive CHECK (quantity >= 0)');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE ticket_types ADD CONSTRAINT check_quantity_positive CHECK (quantity >= 0)');
            }
            // SQLite doesn't support adding constraints after table creation, so we skip it
            // The application-level validation will handle this
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            // Drop the check constraint
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE ticket_types DROP CONSTRAINT check_quantity_positive');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE ticket_types DROP CONSTRAINT check_quantity_positive');
            }
            // SQLite doesn't support dropping constraints, so we skip it
        });
    }
};
