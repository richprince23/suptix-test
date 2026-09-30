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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // auto filled. check boot method in event model
            $table->bigInteger('organizer_id')->constrained();
            $table->bigInteger('venue_id')->constrained();
            $table->string('title');
            $table->string('event_type')->nullable();
            $table->string('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published', 'live', 'canceled', 'past'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
