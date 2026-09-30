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
        Schema::create('organizer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('name')->nullable();
            $table->timestamp('subscribed_at');
            $table->timestamps();
            
            // Ensure one email per organizer
            $table->unique(['organizer_id', 'email']);
            $table->index(['organizer_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_subscriptions');
    }
};
