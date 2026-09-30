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
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id');
            $table->foreignId('organizer_id');
            $table->string('guest_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('guest_code');
            $table->text('notes')->nullable();
            $table->string('referral_source')->nullable();

            $table->enum('status', ['attending', 'maybe', 'not_attending'])->default('attending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
