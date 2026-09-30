<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Unique ticket identifier
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('cascade');
            $table->string('attendee_name');
            // $table->string('attendee_last_name');
            $table->string('attendee_email')->nullable(); // Optional individual email
            $table->decimal('price', 10, 2); // Price at time of purchase
            $table->enum('status', ['active', 'used', 'cancelled', 'refunded', 'pending'])->default('active');
            $table->timestamp('used_at')->nullable(); // When ticket was scanned/used
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['event_id', 'status']);
            $table->index(['ticket_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
