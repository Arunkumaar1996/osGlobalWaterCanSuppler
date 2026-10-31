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
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 50); // e.g. "Early Morning", "Late Morning", "Afternoon", "Evening"
            $table->time('start_time'); // e.g. 08:00:00
            $table->time('end_time');   // e.g. 10:00:00
            $table->integer('cutoff_lead_minutes')->default(60); // Must book at least 60 mins before start_time
            $table->integer('max_orders_capacity')->default(25); // Max distinct customer stops
            $table->integer('max_litres_capacity')->default(1000); // Max litres of water route can carry
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
        });

        Schema::create('delivery_slot_capacity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_slot_id')->constrained('delivery_slots')->cascadeOnDelete();
            $table->date('date')->index();
            $table->integer('reserved_orders_count')->default(0);
            $table->integer('reserved_litres_volume')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            $table->unique(['delivery_slot_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_slot_capacity');
        Schema::dropIfExists('delivery_slots');
    }
};
