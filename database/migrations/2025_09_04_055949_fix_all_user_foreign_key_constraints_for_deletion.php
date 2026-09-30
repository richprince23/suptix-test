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
        // Fix payment_infos.approved_by constraint
        Schema::table('payment_infos', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Fix rsvps.checked_in_by constraint
        Schema::table('rsvps', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->foreign('checked_in_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Fix discount_users.user_id constraint (cascade delete the discount assignment)
        Schema::table('discount_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        // Fix orders.created_by_user_id constraint  
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->foreign('created_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Fix purchases.user_id constraint (cascade delete the purchase)
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert payment_infos.approved_by constraint
        Schema::table('payment_infos', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users');
        });

        // Revert rsvps.checked_in_by constraint
        Schema::table('rsvps', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->foreign('checked_in_by')
                  ->references('id')
                  ->on('users');
        });

        // Revert discount_users.user_id constraint
        Schema::table('discount_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });

        // Revert orders.created_by_user_id constraint
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->foreign('created_by_user_id')
                  ->references('id')
                  ->on('users');
        });

        // Revert purchases.user_id constraint
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }
};
