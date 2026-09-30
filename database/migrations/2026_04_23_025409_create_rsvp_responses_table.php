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
        Schema::create('rsvp_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rsvp_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_form_field_id')->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            $table->index(['rsvp_id', 'event_form_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rsvp_responses');
    }
};
