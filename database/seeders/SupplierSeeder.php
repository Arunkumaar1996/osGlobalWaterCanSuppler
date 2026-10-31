<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplier1 = Supplier::firstOrCreate(
            ['company_name' => 'Crystal Spring Bottlers & Refillers Pvt Ltd'],
            [
                'contact_person' => 'M. S. Venkatesh',
                'phone' => '+91 98401 55551',
                'email' => 'sales@crystalspring.in',
                'gst_number' => '33AABCC1234D1Z5',
                'pan_number' => 'AABCC1234D',
                'address' => 'Plot 18, SIDCO Water Treatment Zone, Maraimalai Nagar',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'pincode' => '603209',
                'bank_name' => 'HDFC Bank',
                'bank_account_no' => '50200012345678',
                'bank_ifsc_code' => 'HDFC0001234',
                'outstanding_payable_balance' => 0.00,
                'notes' => 'Primary RO bottling and automated washing facility partner',
                'is_active' => true,
            ]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['company_name' => 'Nilgiri Pure Springs Plant'],
            [
                'contact_person' => 'K. R. Anand',
                'phone' => '+91 98402 77772',
                'email' => 'orders@nilgiripure.com',
                'gst_number' => '33XYZAB9876C1Z9',
                'pan_number' => 'XYZAB9876C',
                'address' => 'Survey 45/2, Sriperumbudur Industrial Corridor',
                'city' => 'Kanchipuram',
                'state' => 'Tamil Nadu',
                'pincode' => '602105',
                'bank_name' => 'State Bank of India',
                'bank_account_no' => '3000987654321',
                'bank_ifsc_code' => 'SBIN0004567',
                'outstanding_payable_balance' => 0.00,
                'notes' => 'Secondary replenishment & 5L container specialist',
                'is_active' => true,
            ]
        );

        $product20L = Product::where('slug', '20-litre-bubble-top-water-can')->first();
        $product10L = Product::where('slug', '10-litre-compact-water-can')->first();
        $product5L = Product::where('slug', '5-litre-portable-water-can')->first();

        if ($product20L) {
            SupplierProduct::firstOrCreate(
                ['supplier_id' => $supplier1->id, 'product_id' => $product20L->id],
                [
                    'supplier_sku' => 'SUP1-20L',
                    'purchase_price' => 42.00,
                    'lead_time_hours' => 12,
                    'is_preferred' => true,
                ]
            );

            SupplierProduct::firstOrCreate(
                ['supplier_id' => $supplier2->id, 'product_id' => $product20L->id],
                [
                    'supplier_sku' => 'SUP2-20L',
                    'purchase_price' => 44.00,
                    'lead_time_hours' => 24,
                    'is_preferred' => false,
                ]
            );
        }

        if ($product10L) {
            SupplierProduct::firstOrCreate(
                ['supplier_id' => $supplier1->id, 'product_id' => $product10L->id],
                [
                    'supplier_sku' => 'SUP1-10L',
                    'purchase_price' => 28.00,
                    'lead_time_hours' => 12,
                    'is_preferred' => true,
                ]
            );
        }

        if ($product5L) {
            SupplierProduct::firstOrCreate(
                ['supplier_id' => $supplier2->id, 'product_id' => $product5L->id],
                [
                    'supplier_sku' => 'SUP2-5L',
                    'purchase_price' => 18.00,
                    'lead_time_hours' => 24,
                    'is_preferred' => true,
                ]
            );
        }
    }
}
