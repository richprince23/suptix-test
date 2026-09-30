<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'venue_name')) {
                $table->string('venue_name')->nullable()->after('venue_id');
            }
            if (!Schema::hasColumn('events', 'venue_address')) {
                $table->string('venue_address')->nullable()->after('venue_name');
            }
            if (!Schema::hasColumn('events', 'venue_gps_code')) {
                $table->string('venue_gps_code')->nullable()->after('venue_address');
            }
            if (!Schema::hasColumn('events', 'venue_capacity')) {
                $table->unsignedInteger('venue_capacity')->nullable()->after('venue_gps_code');
            }
            if (!Schema::hasColumn('events', 'venue_phone')) {
                $table->string('venue_phone')->nullable()->after('venue_capacity');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'venue_id')) {
                $table->dropColumn('venue_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('venue_id')->nullable()->after('organizer_id');
            $table->dropColumn(['venue_name', 'venue_address', 'venue_gps_code', 'venue_capacity', 'venue_phone']);
        });
    }
};
