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
        // Check if organizer_id column already exists
        if (! Schema::hasColumn('ticket_types', 'organizer_id')) {
            Schema::table('ticket_types', function (Blueprint $table) {
                $table->foreignId('organizer_id')->after('id')->nullable()->constrained()->onDelete('cascade');
                $table->index(['organizer_id']);
            });
        }

        // Update existing ticket_types with organizer_id from their events
        DB::statement('
            UPDATE ticket_types 
            SET organizer_id = (
                SELECT organizer_id 
                FROM events 
                WHERE events.id = ticket_types.event_id
            )
            WHERE organizer_id IS NULL
        ');

        // Make organizer_id not nullable after updating existing records
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropColumn('organizer_id');
        });
    }
};
