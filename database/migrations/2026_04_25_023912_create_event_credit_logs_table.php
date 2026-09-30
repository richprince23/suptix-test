<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_credit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rsvp_id')->nullable()->constrained('rsvps')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('event_credit_purchases')->nullOnDelete();

            $table->enum('type', [
                'attendee_consumed',
                'email_consumed',
                'sms_consumed',
                'attendee_granted',
                'email_granted',
                'sms_granted',
                'attendee_refunded',
            ]);

            $table->enum('source', ['free_tier', 'purchase', 'admin_grant']);

            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('balance_before');
            $table->unsignedInteger('balance_after');
            $table->string('note')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_credit_logs');
    }
};
