<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The Paystack dispute webhook sets this status, but the enum did not allow it
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','processing','completed','failed','refunded','disputed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE payments SET status = 'failed' WHERE status = 'disputed'");
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','processing','completed','failed','refunded') NOT NULL DEFAULT 'pending'");
    }
};
