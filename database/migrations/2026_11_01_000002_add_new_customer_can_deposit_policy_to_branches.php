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
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('charge_new_customer_deposit')->default(false)->after('pincode');
            $table->decimal('new_customer_deposit_amount', 12, 2)->default(0.00)->after('charge_new_customer_deposit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['charge_new_customer_deposit', 'new_customer_deposit_amount']);
        });
    }
};
