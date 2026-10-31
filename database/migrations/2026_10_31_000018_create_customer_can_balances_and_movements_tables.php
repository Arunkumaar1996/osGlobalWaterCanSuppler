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
        Schema::create('customer_can_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->foreignId('can_type_id')->constrained('can_types')->restrictOnDelete();
            $table->integer('held_cans_count')->default(0);
            $table->timestamps();

            $table->unique(['customer_profile_id', 'can_type_id'], 'cust_can_bal_unique');
        });

        Schema::create('can_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('can_type_id')->constrained('can_types')->restrictOnDelete();
            $table->enum('movement_type', [
                'delivery_exchange',
                'return_collection',
                'audit_reconciliation',
                'missing_penalty',
                'damaged_return',
                'initial_deposit'
            ])->index();
            $table->integer('cans_delivered_full')->default(0);
            $table->integer('cans_collected_empty')->default(0);
            $table->integer('missing_cans')->default(0);
            $table->integer('balance_after_movement');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_profile_id', 'can_type_id'], 'can_mov_cust_type_idx');
            $table->index(['branch_id', 'movement_type'], 'can_mov_branch_type_idx');
            $table->index(['reference_type', 'reference_id'], 'can_mov_ref_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('can_movements');
        Schema::dropIfExists('customer_can_balances');
    }
};
