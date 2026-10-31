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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->string('idempotency_key', 100)->unique();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('delivery_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->json('snapshot_address'); // Immutable copy of customer address at order time
            
            // Decoupled independent business states
            $table->enum('order_status', [
                'pending', 'confirmed', 'processing', 'partially_fulfilled', 
                'ready_for_delivery', 'assigned', 'out_for_delivery', 
                'delivered', 'partially_delivered', 'failed', 'cancelled'
            ])->default('pending')->index();

            $table->enum('fulfillment_status', [
                'unfulfilled', 'partially_fulfilled', 'fulfilled'
            ])->default('unfulfilled')->index();

            $table->enum('payment_status', [
                'unpaid', 'partially_paid', 'paid', 'refunded'
            ])->default('unpaid')->index();

            $table->enum('delivery_status', [
                'pending', 'assigned', 'out_for_delivery', 
                'delivered', 'partially_delivered', 'failed', 'rescheduled'
            ])->default('pending')->index();

            // Financial breakdown (DECIMAL 12,2)
            $table->decimal('subtotal_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('delivery_charge', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('previous_outstanding_at_order', 12, 2)->default(0.00);

            // Payment & Scheduling details
            $table->enum('payment_method_preferred', ['razorpay', 'cod', 'pay_later'])->default('cod');
            $table->date('delivery_date')->index();
            $table->foreignId('delivery_slot_id')->constrained('delivery_slots')->restrictOnDelete();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->boolean('is_subscription_order')->default(false);
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'order_status', 'delivery_date']);
            $table->index(['customer_profile_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name_snapshot', 191);
            $table->foreignId('can_type_id')->nullable()->constrained('can_types')->nullOnDelete();
            $table->integer('quantity');
            $table->integer('delivered_quantity')->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate_percent', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->string('comment', 255)->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
