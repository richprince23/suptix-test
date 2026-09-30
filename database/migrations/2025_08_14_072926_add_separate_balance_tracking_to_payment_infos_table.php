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
            // Separate balance tracking for subaccount and system admin
            $table->decimal('subaccount_balance', 15, 2)->default(0)->after('ledger_balance');
            $table->decimal('system_admin_balance', 15, 2)->default(0)->after('subaccount_balance');
            $table->decimal('total_transaction_volume', 15, 2)->default(0)->after('system_admin_balance');

            // Transaction count tracking
            $table->unsignedInteger('total_transactions')->default(0)->after('total_transaction_volume');
            $table->unsignedInteger('successful_transactions')->default(0)->after('total_transactions');
            $table->unsignedInteger('pending_transactions')->default(0)->after('successful_transactions');

            // Last transaction details for better tracking
            $table->timestamp('last_transaction_at')->nullable()->after('pending_transactions');
            $table->string('last_transaction_reference')->nullable()->after('last_transaction_at');
            $table->decimal('last_transaction_amount', 15, 2)->nullable()->after('last_transaction_reference');

            // Balance synchronization tracking
            $table->timestamp('subaccount_balance_last_synced')->nullable()->after('balance_last_updated');
            $table->json('balance_sync_log')->nullable()->after('subaccount_balance_last_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_infos', function (Blueprint $table) {
            $table->dropColumn([
                'subaccount_balance',
                'system_admin_balance',
                'total_transaction_volume',
                'total_transactions',
                'successful_transactions',
                'pending_transactions',
                'last_transaction_at',
                'last_transaction_reference',
                'last_transaction_amount',
                'subaccount_balance_last_synced',
                'balance_sync_log',
            ]);
        });
    }
};
