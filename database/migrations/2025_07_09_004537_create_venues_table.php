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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Ghana');
            $table->string('gps_code')->nullable();
            // $table->string('postal_code')->nullable();
            $table->integer('capacity')->nullable();
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            // $table->foreignId('team_id')->constrained()->onDelete('cascade');
            // $table->string('contact_person')->nullable();
            // $table->string('contact_person_email')->nullable();
            // $table->string('contact_person_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
