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
        Schema::create('can_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('capacity_litres', 6, 2);
            $table->string('material', 50)->default('polycarbonate');
            $table->decimal('deposit_amount', 12, 2)->default(150.00); // Standard missing/damaged replacement valuation
            $table->boolean('is_reusable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('can_type_id')->nullable()->constrained('can_types')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 50)->unique();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('capacity_value', 8, 2);
            $table->string('capacity_unit', 20)->default('Litre');
            $table->decimal('selling_price', 12, 2);
            $table->decimal('tax_rate_percent', 5, 2)->default(18.00);
            $table->boolean('is_tax_inclusive')->default(true);
            $table->integer('min_order_quantity')->default(1);
            $table->integer('max_order_quantity')->default(50);
            $table->boolean('requires_can_exchange')->default(true);
            $table->string('image_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'is_active', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('can_types');
    }
};
