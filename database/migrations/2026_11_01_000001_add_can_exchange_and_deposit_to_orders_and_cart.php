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
        Schema::table('carts', function (Blueprint $table) {
            $table->boolean('has_empty_cans_to_exchange')->default(true)->after('branch_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_empty_cans_to_exchange')->default(true)->after('payment_method_preferred');
            $table->decimal('deposit_amount', 12, 2)->default(0.00)->after('discount_amount');
            $table->integer('empty_cans_expected')->default(0)->after('has_empty_cans_to_exchange');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['has_empty_cans_to_exchange', 'deposit_amount', 'empty_cans_expected']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('has_empty_cans_to_exchange');
        });
    }
};
