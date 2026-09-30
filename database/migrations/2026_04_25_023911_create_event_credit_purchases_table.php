<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_credit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();

            $table->string('paystack_reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('pack_key', 50);
            $table->unsignedSmallInteger('quantity')->default(1);

            $table->integer('attendee_slots_granted')->default(0);
            $table->integer('email_credits_granted')->default(0);
            $table->integer('sms_credits_granted')->default(0);

            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_credit_purchases');
    }
};
