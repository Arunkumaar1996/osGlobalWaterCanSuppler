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
        // 1. Payments Table (Covers Razorpay, COD collections, UPI, Bank Transfer)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50); // razorpay, cod_cash, upi_collected, bank_transfer, credit_adjustment
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending')->index();
            $table->string('transaction_reference', 150)->nullable(); // Razorpay payment ID (pay_xxxx), UTR, etc.
            $table->string('gateway_order_id', 150)->nullable();     // Razorpay order ID (order_xxxx)
            $table->string('gateway_signature', 255)->nullable();
            $table->date('payment_date');
            $table->foreignId('collected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_profile_id', 'status', 'payment_date']);
            $table->index(['gateway_order_id', 'transaction_reference']);
        });

        // 2. Customer Financial Ledger (Double-Entry: Strict segregation from can balances)
        Schema::create('customer_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->string('transaction_type', 50); // order_charge, payment_received, credit_adjustment, debit_adjustment, refund
            $table->nullableMorphs('reference');    // Order, Payment, etc.
            $table->decimal('debit_amount', 12, 2)->default(0.00);  // Increases receivable due from customer
            $table->decimal('credit_amount', 12, 2)->default(0.00); // Decreases receivable due (payment/settlement)
            $table->decimal('running_balance', 12, 2);              // Cumulative outstanding financial due
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['customer_profile_id', 'created_at'], 'cust_ledger_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ledger');
        Schema::dropIfExists('payments');
    }
};
