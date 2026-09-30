<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_form_field_id')->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            $table->index(['order_id', 'event_form_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_responses');
    }
};
