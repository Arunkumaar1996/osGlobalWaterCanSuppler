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
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('designation', 100);
            $table->string('vehicle_number', 50)->nullable();
            $table->string('driving_license_no', 50)->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->decimal('current_cash_in_hand', 12, 2)->default(0.00);
            $table->boolean('is_available_for_delivery')->default(true)->index();
            $table->timestamps();

            $table->index(['branch_id', 'is_available_for_delivery']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
