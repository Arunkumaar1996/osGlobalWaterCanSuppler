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
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_pincodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_zone_id')->constrained('delivery_zones')->cascadeOnDelete();
            $table->string('pincode', 10)->index();
            $table->string('area_name', 150);
            $table->decimal('delivery_charge', 12, 2)->default(0.00);
            $table->decimal('min_order_amount', 12, 2)->default(0.00);
            $table->boolean('is_serviceable')->default(true)->index();
            $table->timestamps();

            $table->index(['pincode', 'is_serviceable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_pincodes');
        Schema::dropIfExists('delivery_zones');
    }
};
