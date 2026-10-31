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
        // 1. Add fulfilled_quantity column to order_items if not present
        if (!Schema::hasColumn('order_items', 'fulfilled_quantity')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->integer('fulfilled_quantity')->default(0)->after('quantity');
            });
        }

        // 2. Order Fulfillments (Split sourcing allocation units)
        Schema::create('order_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->string('fulfillment_number', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('source_type', ['warehouse', 'supplier'])->default('warehouse');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->enum('status', [
                'pending', 'allocated', 'ready_for_dispatch', 'dispatched', 'completed', 'cancelled'
            ])->default('pending')->index();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['source_type', 'status']);
        });

        // 3. Order Fulfillment Items (Line items bound to each sourcing unit)
        Schema::create('order_fulfillment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_fulfillment_id')->constrained('order_fulfillments')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            $table->index(['order_fulfillment_id', 'product_id'], 'ful_item_prod_idx');
        });

        // 4. Dispatch Manifests (Packing slips and batch departure manifests)
        Schema::create('dispatch_manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number', 50)->unique();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->enum('status', ['draft', 'generated', 'dispatched', 'closed'])->default('generated')->index();
            $table->string('vehicle_number', 50)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('dispatched_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Dispatch Manifest Fulfillments (Fulfillments grouped under a manifest)
        Schema::create('dispatch_manifest_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_manifest_id')->constrained('dispatch_manifests')->cascadeOnDelete();
            $table->foreignId('order_fulfillment_id')->constrained('order_fulfillments')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['dispatch_manifest_id', 'order_fulfillment_id'], 'manifest_ful_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_manifest_fulfillments');
        Schema::dropIfExists('dispatch_manifests');
        Schema::dropIfExists('order_fulfillment_items');
        Schema::dropIfExists('order_fulfillments');

        if (Schema::hasColumn('order_items', 'fulfilled_quantity')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('fulfilled_quantity');
            });
        }
    }
};
