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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_number', 50)->unique();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('delivery_address_id')->constrained('customer_addresses')->restrictOnDelete();
            $table->foreignId('delivery_slot_id')->constrained('delivery_slots')->restrictOnDelete();
            $table->enum('frequency', ['daily', 'alternate_days', 'weekly', 'monthly', 'custom_days'])->default('daily')->index();
            $table->json('custom_days')->nullable(); // e.g. ["mon", "wed", "fri"]
            $table->enum('status', ['active', 'paused', 'cancelled', 'completed'])->default('active')->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_delivery_date')->index();
            $table->date('last_generated_date')->nullable();
            $table->enum('payment_method', ['cod', 'pay_later', 'razorpay'])->default('cod');
            $table->text('customer_notes')->nullable();
            $table->text('pause_reason')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status', 'next_delivery_date']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
    }
};
