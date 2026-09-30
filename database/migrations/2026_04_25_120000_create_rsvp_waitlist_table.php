<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvp_waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('referral_source')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('waitlist_code', 12)->unique();
            $table->unsignedInteger('position');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['email', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvp_waitlist');
    }
};
