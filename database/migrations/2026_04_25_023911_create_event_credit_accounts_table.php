<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();

            $table->smallInteger('free_attendee_slots')->default(100);
            $table->smallInteger('free_email_credits')->default(100);
            $table->smallInteger('free_sms_credits')->default(0);

            $table->integer('purchased_attendee_slots')->default(0);
            $table->integer('purchased_email_credits')->default(0);
            $table->integer('purchased_sms_credits')->default(0);

            $table->integer('used_attendee_slots')->default(0);
            $table->integer('used_email_credits')->default(0);
            $table->integer('used_sms_credits')->default(0);

            $table->date('global_daily_email_date')->nullable();
            $table->smallInteger('global_daily_email_count')->default(0);

            $table->enum('overflow_behavior', ['block', 'waitlist'])->default('block');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_credit_accounts');
    }
};
