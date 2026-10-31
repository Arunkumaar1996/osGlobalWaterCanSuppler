<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Business;
use App\Models\DeliveryPincode;
use App\Models\DeliveryZone;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class BusinessAndBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business = Business::firstOrCreate(
            ['email' => 'contact@aquaflow.com'],
            [
                'name' => 'AquaFlow Pure Water Solutions',
                'legal_name' => 'AquaFlow Beverages & Distribution Pvt Ltd',
                'gst_number' => '33AAAAA0000A1Z5',
                'pan_number' => 'AAAAA0000A',
                'phone' => '+91 98765 00001',
                'address_line1' => 'Plot 42, Industrial Estate Phase 2',
                'address_line2' => 'Guindy',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'pincode' => '600032',
                'is_active' => true,
            ]
        );

        $branch = Branch::firstOrCreate(
            ['code' => 'BR-CHE-001'],
            [
                'business_id' => $business->id,
                'name' => 'Central Chennai Distribution Hub',
                'phone' => '+91 98765 00002',
                'email' => 'chennai.central@aquaflow.com',
                'address' => 'Plot 42, Industrial Estate Phase 2, Guindy',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'pincode' => '600032',
                'latitude' => 13.0067,
                'longitude' => 80.2025,
                'is_primary' => true,
                'is_active' => true,
            ]
        );

        Warehouse::firstOrCreate(
            ['code' => 'WH-CHE-01'],
            [
                'branch_id' => $branch->id,
                'name' => 'Guindy Central Warehouse & Sanitization Hub',
                'address' => 'Plot 42, Warehouse Wing B, Guindy, Chennai',
                'contact_person' => 'K. Ramanathan',
                'phone' => '+91 98765 00003',
                'is_active' => true,
            ]
        );

        Warehouse::firstOrCreate(
            ['code' => 'WH-CHE-02'],
            [
                'branch_id' => $branch->id,
                'name' => 'Velachery South Satellite Depot',
                'address' => '15/3 Bypass Road, Velachery, Chennai',
                'contact_person' => 'M. Senthil',
                'phone' => '+91 98765 00004',
                'is_active' => true,
            ]
        );

        $zoneA = DeliveryZone::firstOrCreate(
            ['code' => 'ZONE-CHE-CENTRAL'],
            [
                'branch_id' => $branch->id,
                'name' => 'Central Metro Sector',
                'description' => 'Guindy, Saidapet, T. Nagar, Nandanam',
                'is_active' => true,
            ]
        );

        $zoneB = DeliveryZone::firstOrCreate(
            ['code' => 'ZONE-CHE-SOUTH'],
            [
                'branch_id' => $branch->id,
                'name' => 'South Coastal Sector',
                'description' => 'Adyar, Besant Nagar, Thiruvanmiyur, Velachery',
                'is_active' => true,
            ]
        );

        $pincodes = [
            // Zone A
            ['zone_id' => $zoneA->id, 'pincode' => '600032', 'area' => 'Guindy Industrial Estate', 'charge' => 0.00, 'min_order' => 150.00],
            ['zone_id' => $zoneA->id, 'pincode' => '600015', 'area' => 'Saidapet & Little Mount', 'charge' => 0.00, 'min_order' => 150.00],
            ['zone_id' => $zoneA->id, 'pincode' => '600017', 'area' => 'T. Nagar Commercial Hub', 'charge' => 20.00, 'min_order' => 200.00],
            ['zone_id' => $zoneA->id, 'pincode' => '600035', 'area' => 'Nandanam Extension', 'charge' => 20.00, 'min_order' => 200.00],
            // Zone B
            ['zone_id' => $zoneB->id, 'pincode' => '600020', 'area' => 'Adyar Residential Hub', 'charge' => 15.00, 'min_order' => 150.00],
            ['zone_id' => $zoneB->id, 'pincode' => '600041', 'area' => 'Thiruvanmiyur & ECR Gateway', 'charge' => 25.00, 'min_order' => 250.00],
            ['zone_id' => $zoneB->id, 'pincode' => '600042', 'area' => 'Velachery Main', 'charge' => 20.00, 'min_order' => 200.00],
            ['zone_id' => $zoneB->id, 'pincode' => '600096', 'area' => 'Perungudi OMR Corridor', 'charge' => 30.00, 'min_order' => 300.00],
        ];

        foreach ($pincodes as $pin) {
            DeliveryPincode::firstOrCreate(
                ['pincode' => $pin['pincode']],
                [
                    'delivery_zone_id' => $pin['zone_id'],
                    'area_name' => $pin['area'],
                    'delivery_charge' => $pin['charge'],
                    'min_order_amount' => $pin['min_order'],
                    'is_serviceable' => true,
                ]
            );
        }
    }
}
