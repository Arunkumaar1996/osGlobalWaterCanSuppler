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
        // 3rd-Party Delivery Partner Organizations (Separate from Suppliers)
        Schema::create('delivery_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('name', 150); // e.g. "Porter Express", "Shadowfax Quick", "Dunzo Fleet"
            $table->string('contact_person', 150);
            $table->string('contact_phone', 20);
            $table->string('contact_email')->nullable();
            $table->enum('billing_type', ['per_order', 'per_can', 'per_km', 'monthly_fixed'])->default('per_order');
            $table->decimal('rate_per_unit', 10, 2)->default(35.00); // e.g. ₹35 per delivery
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
        });

        // Delivery Trips / Route Runsheets
        Schema::create('delivery_trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_number', 50)->unique(); // e.g. TRIP-20261101-0001
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('delivery_slot_id')->constrained('delivery_slots')->restrictOnDelete();
            $table->date('trip_date')->index();
            $table->enum('driver_type', ['staff', 'partner'])->default('staff');
            $table->foreignId('staff_profile_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->foreignId('delivery_partner_id')->nullable()->constrained('delivery_partners')->nullOnDelete();
            $table->string('vehicle_number', 50)->nullable();
            $table->enum('status', ['scheduled', 'in_transit', 'completed', 'cancelled'])->default('scheduled')->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('total_orders_count')->default(0);
            $table->decimal('total_litres_payload', 10, 2)->default(0.00);
            $table->decimal('cash_collected', 12, 2)->default(0.00);
            $table->integer('empty_cans_collected')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'trip_date', 'status']);
        });

        // Trip Stops (Sequential order stops with OTP, can collection, and COD entry)
        Schema::create('delivery_trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_trip_id')->constrained('delivery_trips')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->integer('sequence_order')->default(1);
            $table->enum('status', ['pending', 'arrived', 'delivered', 'failed', 'rescheduled'])->default('pending')->index();
            $table->string('delivery_otp', 10); // 4 or 6 digit verification PIN
            $table->dateTime('otp_verified_at')->nullable();
            $table->integer('empty_cans_collected')->default(0);
            $table->decimal('cash_collected', 12, 2)->default(0.00);
            $table->string('failure_reason')->nullable();
            $table->string('customer_signature_or_photo')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['delivery_trip_id', 'order_id']);
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_trip_stops');
        Schema::dropIfExists('delivery_trips');
        Schema::dropIfExists('delivery_partners');
    }
};
