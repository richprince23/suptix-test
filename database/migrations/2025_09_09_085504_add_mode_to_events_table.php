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
        Schema::table('events', function (Blueprint $table) {

            $table->enum('mode', ['online', 'in-person', 'hybrid'])->default('in-person');
            $table->unsignedBigInteger('venue_id')->nullable()->change();
            $table->string('platform_name')->nullable();
            $table->string('online_link')->nullable();
            $table->boolean('is_public_link')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('mode');
            $table->foreignId('venue_id')->constrained('venue');
            $table->string('platform_name')->nullable();
            $table->string('online_link')->nullable();
            $table->boolean('is_public_link')->default(false);
        });
    }
};
