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
            $table->enum('transaction_fee_payer', ['account', 'subaccount'])
                ->default('subaccount')
                ->after('percentage_charge')
                ->comment('Determines who pays transaction fees - account (main account) or subaccount (organizer)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_infos', function (Blueprint $table) {
            $table->dropColumn('transaction_fee_payer');
        });
    }
};
