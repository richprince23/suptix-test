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
        Schema::create('payment_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->onDelete('cascade');

            // Paystack subaccount details
            $table->string('paystack_subaccount_code')->unique()->nullable();
            $table->string('business_name');
            $table->string('settlement_bank');
            $table->string('account_number');
            $table->string('bank_code');
            $table->string('payment_channel')->default('bank'); // bank or mobile_money
            $table->decimal('percentage_charge', 5, 2)->default(7.00);
            $table->json('metadata')->nullable();

            // Account status and verification
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();

            // Balance information
            $table->decimal('available_balance', 15, 2)->default(0.00);
            $table->decimal('ledger_balance', 15, 2)->default(0.00);
            $table->timestamp('balance_last_updated')->nullable();

            // Settlement information
            $table->string('settlement_schedule')->default('auto'); // auto, weekly, monthly
            $table->decimal('minimum_settlement_amount', 10, 2)->default(0.00);

            // API integration
            $table->timestamp('subaccount_created_at')->nullable();
            $table->json('paystack_response')->nullable(); // Store full Paystack response

            $table->timestamps();

            // Indexes
            $table->index(['organizer_id', 'is_active']);
            $table->index('paystack_subaccount_code');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_infos');
    }
};
