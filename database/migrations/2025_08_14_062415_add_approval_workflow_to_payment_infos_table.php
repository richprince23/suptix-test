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
            // Approval workflow fields
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Account verification fields from Paystack
            $table->string('account_name')->nullable();
            $table->boolean('paystack_verified')->default(false);
            $table->json('verification_response')->nullable();

            // Notification tracking
            $table->boolean('admin_notified')->default(false);
            $table->timestamp('admin_notified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_infos', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'approved_by',
                'approved_at',
                'rejection_reason',
                'submitted_at',
                'account_name',
                'paystack_verified',
                'verification_response',
                'admin_notified',
                'admin_notified_at',
            ]);
        });
    }
};
