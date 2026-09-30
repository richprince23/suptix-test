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
        Schema::table('event_credit_logs', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->change();
            $table->unsignedInteger('balance_before')->change();
            $table->unsignedInteger('balance_after')->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_credit_logs', function (Blueprint $table) {
            $table->tinyInteger('quantity')->default(1)->change();
            $table->integer('balance_before')->change();
            $table->integer('balance_after')->change();
        });
    }
};
