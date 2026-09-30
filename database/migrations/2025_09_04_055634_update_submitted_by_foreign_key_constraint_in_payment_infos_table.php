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
        Schema::table('payment_infos', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['submitted_by']);
            
            // Re-add the foreign key with onDelete set to nullify
            $table->foreign('submitted_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_infos', function (Blueprint $table) {
            // Drop the modified foreign key constraint
            $table->dropForeign(['submitted_by']);
            
            // Re-add the original foreign key constraint (restrict)
            $table->foreign('submitted_by')
                  ->references('id')
                  ->on('users');
        });
    }
};
