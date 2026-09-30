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
        Schema::table('event_people', function (Blueprint $table) {
            $table->foreignId('session_id')->nullable()->after('event_id')
                ->constrained('event_sessions')->nullOnDelete()
                ->comment('Optionally link this person to a specific session');
        });
    }

    public function down(): void
    {
        Schema::table('event_people', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};
