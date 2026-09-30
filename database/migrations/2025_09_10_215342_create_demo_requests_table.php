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
        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('company');
            $table->string('job_title');
            $table->string('company_size');
            $table->json('event_types');
            $table->string('events_per_year');
            $table->string('average_attendees');
            $table->string('current_solution')->nullable();
            $table->text('demo_focus');
            $table->string('timeline');
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->boolean('newsletter')->default(false);
            $table->boolean('terms')->default(false);
            $table->string('honeypot')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo_requests');
    }
};
