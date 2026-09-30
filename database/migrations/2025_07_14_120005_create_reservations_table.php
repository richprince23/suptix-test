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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_type_id')->constrained()->onDelete('cascade');
            $table->string('reservation_number')->unique();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone')->nullable();
            $table->integer('party_size');
            $table->timestamp('reserved_at');
            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organizer_id', 'status']);
            $table->index(['event_id', 'reserved_at']);
            $table->index(['reservation_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
