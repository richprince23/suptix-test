<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvp_credit_settings', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('free_attendee_slots_default')->default(100);
            $table->smallInteger('free_email_credits_default')->default(100);
            $table->smallInteger('free_sms_credits_default')->default(0);
            $table->smallInteger('brevo_daily_limit')->default(270);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp_credit_settings');
    }
};
