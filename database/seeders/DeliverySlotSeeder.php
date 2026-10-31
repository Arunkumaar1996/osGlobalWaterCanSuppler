<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\DeliverySlot;
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();
        if (! $branch) {
            return;
        }

        $slots = [
            [
                'name' => 'Early Morning Window (8 AM - 10 AM)',
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'cutoff_lead_minutes' => 60,
                'max_orders_capacity' => 30,
                'max_litres_capacity' => 1200,
                'is_active' => true,
            ],
            [
                'name' => 'Late Morning Window (10 AM - 12 PM)',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'cutoff_lead_minutes' => 60,
                'max_orders_capacity' => 25,
                'max_litres_capacity' => 1000,
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon Window (2 PM - 4 PM)',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'cutoff_lead_minutes' => 60,
                'max_orders_capacity' => 25,
                'max_litres_capacity' => 1000,
                'is_active' => true,
            ],
            [
                'name' => 'Evening Express Window (4 PM - 6 PM)',
                'start_time' => '16:00:00',
                'end_time' => '18:00:00',
                'cutoff_lead_minutes' => 60,
                'max_orders_capacity' => 35,
                'max_litres_capacity' => 1500,
                'is_active' => true,
            ],
        ];

        foreach ($slots as $slot) {
            DeliverySlot::firstOrCreate(
                ['branch_id' => $branch->id, 'name' => $slot['name']],
                $slot
            );
        }
    }
}
