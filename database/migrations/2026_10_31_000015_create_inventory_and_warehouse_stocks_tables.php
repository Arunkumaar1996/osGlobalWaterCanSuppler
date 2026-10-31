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
        // 1. Warehouse Stocks (Multi-state physical container and liquid tracking)
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('can_type_id')->nullable()->constrained('can_types')->nullOnDelete();
            $table->unsignedInteger('full_stock')->default(0);          // Ready for customer dispatch
            $table->unsignedInteger('empty_stock')->default(0);         // Reusable empty cans awaiting wash/refill
            $table->unsignedInteger('under_cleaning')->default(0);      // In wash / sterilization / refilling bay
            $table->unsignedInteger('damaged_stock')->default(0);       // Condemned / cracked / unusable
            $table->unsignedInteger('reserved_stock')->default(0);      // Locked for confirmed dispatching orders
            $table->unsignedInteger('reorder_level')->default(20);      // Minimum threshold before alert
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id'], 'wh_product_unique');
        });

        // 2. Inventory Movements (Immutable double-entry physical container audit log)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('can_type_id')->nullable()->constrained('can_types')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('movement_type', 50); // purchase_received, order_dispatched, empty_collected, sent_to_cleaning, refilled_to_full, damaged_writeoff, transfer_out, transfer_in, stock_adjustment
            $table->string('state_from', 50)->default('none'); // none, full, empty, under_cleaning, damaged, reserved
            $table->string('state_to', 50)->default('none');   // none, full, empty, under_cleaning, damaged, reserved
            $table->integer('quantity'); // Positive integer for count moved
            $table->unsignedInteger('full_stock_after')->default(0);
            $table->unsignedInteger('empty_stock_after')->default(0);
            $table->unsignedInteger('under_cleaning_after')->default(0);
            $table->unsignedInteger('damaged_stock_after')->default(0);
            $table->unsignedInteger('reserved_stock_after')->default(0);
            $table->nullableMorphs('reference'); // e.g. GoodsReceipt, Order, StockTransfer, etc.
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['warehouse_id', 'product_id', 'created_at'], 'inv_wh_prod_time_idx');
        });

        // 3. Inter-Warehouse Stock Transfers
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('status', 30)->default('initiated'); // initiated, in_transit, completed, cancelled
            $table->foreignId('initiated_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Stock Transfer Items
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('can_type_id')->nullable()->constrained('can_types')->nullOnDelete();
            $table->string('stock_type', 30)->default('full'); // full, empty, damaged
            $table->unsignedInteger('quantity_sent');
            $table->unsignedInteger('quantity_received')->default(0);
            $table->unsignedInteger('quantity_damaged_in_transit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('warehouse_stocks');
    }
};
