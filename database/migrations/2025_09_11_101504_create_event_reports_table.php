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
        Schema::create('event_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('organizer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email')->nullable();
            $table->enum('report_type', ['bug', 'content_issue', 'spam', 'inappropriate', 'other'])->default('other');
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');
            $table->json('metadata')->nullable(); // Store additional data like browser, IP, etc.
            $table->timestamp('organizer_notified_at')->nullable();
            $table->timestamp('admin_notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('organizer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('honeypot')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['report_type', 'priority']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_reports');
    }
};
