<?php

namespace Database\Seeders;

use App\Models\CanType;
use App\Models\Faq;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class ProductAndCmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Can Types
        $can20L = CanType::firstOrCreate(
            ['name' => '20L Standard Bubble Top'],
            [
                'capacity_litres' => 20.00,
                'material' => 'Food-Grade Polycarbonate',
                'deposit_amount' => 150.00,
                'is_reusable' => true,
                'is_active' => true,
            ]
        );

        $can10L = CanType::firstOrCreate(
            ['name' => '10L Compact Easy-Pour'],
            [
                'capacity_litres' => 10.00,
                'material' => 'Food-Grade Polycarbonate',
                'deposit_amount' => 100.00,
                'is_reusable' => true,
                'is_active' => true,
            ]
        );

        $can5L = CanType::firstOrCreate(
            ['name' => '5L Mini Handle Can'],
            [
                'capacity_litres' => 5.00,
                'material' => 'PET Food-Grade',
                'deposit_amount' => 60.00,
                'is_reusable' => true,
                'is_active' => true,
            ]
        );

        // 2. Categories
        $catWater = ProductCategory::firstOrCreate(
            ['slug' => 'packaged-drinking-water'],
            [
                'name' => 'Packaged Drinking Water',
                'description' => 'Multi-stage RO & UV purified mineral-enriched water cans',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $catAccessories = ProductCategory::firstOrCreate(
            ['slug' => 'accessories-and-dispensers'],
            [
                'name' => 'Dispensers & Stands',
                'description' => 'Essential water can accessories, dispensers, and pumps',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        // 3. Products
        Product::firstOrCreate(
            ['slug' => '20-litre-bubble-top-water-can'],
            [
                'category_id' => $catWater->id,
                'can_type_id' => $can20L->id,
                'name' => '20 Litre Premium Water Can',
                'sku' => 'CAN-20L-RO',
                'short_description' => 'Most popular family & corporate size with multi-stage mineral purification.',
                'description' => 'Our flagship 20 Litre drinking water can passes through 8 rigorous filtration stages including Reverse Osmosis (RO), Ultra Violet (UV) sterilization, and essential mineral balancing (TDS 100-150 ppm). Ideal for homes, apartments, and commercial offices.',
                'capacity_value' => 20.00,
                'capacity_unit' => 'Litre',
                'selling_price' => 80.00,
                'tax_rate_percent' => 18.00,
                'is_tax_inclusive' => true,
                'min_order_quantity' => 1,
                'max_order_quantity' => 50,
                'requires_can_exchange' => true,
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['slug' => '10-litre-compact-water-can'],
            [
                'category_id' => $catWater->id,
                'can_type_id' => $can10L->id,
                'name' => '10 Litre Compact Water Can',
                'sku' => 'CAN-10L-RO',
                'short_description' => 'Compact, lightweight, easy to lift and replace on top dispensers.',
                'description' => 'Designed for easy handling and smaller kitchens. Weighing just 10kg when full, it is easy to mount on tabletop dispensers or carry up staircases without straining.',
                'capacity_value' => 10.00,
                'capacity_unit' => 'Litre',
                'selling_price' => 55.00,
                'tax_rate_percent' => 18.00,
                'is_tax_inclusive' => true,
                'min_order_quantity' => 1,
                'max_order_quantity' => 30,
                'requires_can_exchange' => true,
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['slug' => '5-litre-portable-water-can'],
            [
                'category_id' => $catWater->id,
                'can_type_id' => $can5L->id,
                'name' => '5 Litre Portable Water Can',
                'sku' => 'CAN-5L-RO',
                'short_description' => 'Ideal for bachelor bedrooms, dining tables, travel, and bedside use.',
                'description' => 'Convenient 5L water container with an integrated carry handle. Perfect for single residents, study rooms, or short weekend family outings.',
                'capacity_value' => 5.00,
                'capacity_unit' => 'Litre',
                'selling_price' => 35.00,
                'tax_rate_percent' => 18.00,
                'is_tax_inclusive' => true,
                'min_order_quantity' => 1,
                'max_order_quantity' => 20,
                'requires_can_exchange' => true,
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['slug' => 'manual-water-dispenser-hand-pump'],
            [
                'category_id' => $catAccessories->id,
                'can_type_id' => null,
                'name' => 'Manual Hand Press Dispenser Pump',
                'sku' => 'ACC-PUMP-01',
                'short_description' => 'Food-grade manual suction pump fits directly on 20L and 10L water cans.',
                'description' => 'Hassle-free manual water pump with no battery or electricity required. Fits securely onto standard bubble top cans for easy, spill-free one-press pouring.',
                'capacity_value' => 0.00,
                'capacity_unit' => 'Unit',
                'selling_price' => 180.00,
                'tax_rate_percent' => 18.00,
                'is_tax_inclusive' => true,
                'min_order_quantity' => 1,
                'max_order_quantity' => 5,
                'requires_can_exchange' => false,
                'is_featured' => false,
                'is_active' => true,
            ]
        );

        // 4. FAQs
        $faqs = [
            [
                'question' => 'How do I order water cans online?',
                'answer' => 'Simply browse our available water can sizes on the homepage, select your desired quantity, choose your preferred delivery date and time slot, and confirm with Cash on Delivery, Razorpay, or Pay Later.',
                'category' => 'ordering',
                'sort_order' => 1,
            ],
            [
                'question' => 'How does the empty-can exchange model work?',
                'answer' => 'We operate on a 1-to-1 container exchange system. When our delivery partner delivers full water cans, you simply return the same number of empty cans of the matching capacity. If you return fewer cans, the outstanding cans are debited to your can account. If you return extra cans, they are credited to your account for future orders.',
                'category' => 'exchange',
                'sort_order' => 2,
            ],
            [
                'question' => 'Can I pay later for my water deliveries?',
                'answer' => 'Yes! We offer a Pay Later credit option for eligible residential and registered commercial B2B accounts with an approved credit limit. You can order today and settle your dues weekly or monthly via Razorpay online payment or cash collection.',
                'category' => 'payment',
                'sort_order' => 3,
            ],
            [
                'question' => 'Can I choose my specific delivery date and time slot?',
                'answer' => 'Absolutely. During checkout, you can select from our convenient daily delivery slots (Morning 8 AM–10 AM, 10 AM–12 PM, Afternoon 2 PM–4 PM, Evening 4 PM–6 PM) based on real-time vehicle route capacity.',
                'category' => 'delivery',
                'sort_order' => 4,
            ],
            [
                'question' => 'What happens if I am unavailable during delivery?',
                'answer' => 'Our delivery executive will call your contact number upon arrival. If you are unavailable, our driver can either leave the cans with your building security (if instructed) or record a failed attempt with photos. You can reschedule to another available slot at zero extra charge with one click from your dashboard.',
                'category' => 'delivery',
                'sort_order' => 5,
            ],
            [
                'question' => 'Which areas do you deliver to?',
                'answer' => 'We currently service central and southern Chennai sectors including Guindy, Saidapet, T. Nagar, Nandanam, Adyar, Besant Nagar, Thiruvanmiyur, Velachery, and Perungudi OMR. You can check your exact 6-digit pincode using our real-time service checker.',
                'category' => 'areas',
                'sort_order' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(['question' => $faq['question']], $faq);
        }

        // 5. Testimonials
        $testimonials = [
            [
                'customer_name' => 'Kavitha Rangarajan',
                'customer_designation_or_area' => 'Resident, Adyar, Chennai',
                'rating' => 5,
                'comment' => 'The morning delivery slot between 8 AM and 10 AM has never failed us in 6 months! Water is crystal clean, and returning empty cans is so straightforward with their delivery staff.',
            ],
            [
                'customer_name' => 'Dinesh Venkatesh',
                'customer_designation_or_area' => 'Facility Manager, InnovateTech Guindy',
                'rating' => 5,
                'comment' => 'We order 40 cans every Monday for our corporate floor. The split-fulfillment and GST-compliant invoicing are seamless. Pay Later credit terms make monthly vendor reconciliation painless.',
            ],
            [
                'customer_name' => 'Arunachalam S.',
                'customer_designation_or_area' => 'Apartment Association Sec, Velachery',
                'rating' => 5,
                'comment' => 'The 10L compact cans are a game changer for our senior citizen residents who cannot lift heavy 20kg cans. Fast booking and OTP-verified doorstep delivery give great peace of mind.',
            ],
        ];

        foreach ($testimonials as $t) {
            Testimonial::firstOrCreate(['customer_name' => $t['customer_name']], $t);
        }
    }
}
