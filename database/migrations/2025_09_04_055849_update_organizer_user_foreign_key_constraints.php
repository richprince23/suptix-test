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
        Schema::table('organizer_user', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['user_id']);
            $table->dropForeign(['organizer_id']);
            
            // Re-add with cascade delete for user_id and restrict for organizer_id
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade'); // Delete pivot record when user is deleted
                  
            $table->foreign('organizer_id')
                  ->references('id')
                  ->on('organizers')
                  ->onDelete('cascade'); // Delete pivot record when organizer is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizer_user', function (Blueprint $table) {
            // Drop modified foreign key constraints
            $table->dropForeign(['user_id']);
            $table->dropForeign(['organizer_id']);
            
            // Re-add original constraints (restrict)
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
                  
            $table->foreign('organizer_id')
                  ->references('id')
                  ->on('organizers');
        });
    }
};
