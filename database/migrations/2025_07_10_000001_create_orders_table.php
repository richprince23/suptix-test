<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Generated order number
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            // $table->string('buyer_name'); // Guest email
            $table->string('buyer_email'); // Guest email
            $table->string('buyer_phone')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable(); // stripe, paypal, etc.
            $table->string('payment_reference')->nullable(); // Payment gateway reference
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['buyer_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
