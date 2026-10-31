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
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->enum('customer_type', ['residential', 'commercial', 'corporate'])->default('residential');
            $table->string('company_name')->nullable();
            $table->string('gst_number', 30)->nullable();
            $table->decimal('outstanding_financial_balance', 12, 2)->default(0.00);
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->boolean('allow_pay_later')->default(false);
            $table->string('referral_code', 30)->nullable()->unique();
            $table->foreignId('referred_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'customer_type']);
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->cascadeOnDelete();
            $table->enum('address_type', ['home', 'office', 'other'])->default('home');
            $table->string('contact_person_name', 150);
            $table->string('contact_person_mobile', 20);
            $table->string('house_flat_no', 100);
            $table->string('building_apartment_name', 150)->nullable();
            $table->string('street_address', 255);
            $table->string('landmark', 150)->nullable();
            $table->string('area', 100);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('pincode', 10)->index();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['customer_profile_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_profiles');
    }
};
