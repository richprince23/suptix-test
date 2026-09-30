<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->boolean('checked_in')->default(false)->after('status');
            $table->timestamp('checked_in_at')->nullable()->after('checked_in');
            $table->foreignId('checked_in_by')->nullable()->after('checked_in_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->dropColumn(['checked_in', 'checked_in_at', 'checked_in_by']);
        });
    }
};
