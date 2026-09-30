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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('organizer_id')->constrained('users')->onDelete('set null');
            $table->boolean('is_admin_order')->default(false)->after('created_by_user_id');
            $table->text('admin_notes')->nullable()->after('is_admin_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'is_admin_order', 'admin_notes']);
        });
    }
};
