<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vehicle Fuel & CNG',
                'slug' => 'vehicle-fuel',
                'description' => 'Petrol, diesel, CNG expenses for delivery fleet vans and bikes',
            ],
            [
                'name' => 'Vehicle Repairs & Maintenance',
                'slug' => 'vehicle-maintenance',
                'description' => 'Servicing, tire replacements, vehicle insurance and fitness tests',
            ],
            [
                'name' => 'Plant & Facility Rent',
                'slug' => 'facility-rent',
                'description' => 'Monthly lease or rent for water purification plant and branch hubs',
            ],
            [
                'name' => 'Electricity & Utilities',
                'slug' => 'utilities',
                'description' => 'Power, water charges, internet, and office utilities',
            ],
            [
                'name' => 'Staff Salaries & Daily Wages',
                'slug' => 'salaries-wages',
                'description' => 'Plant operators, loading crew, and delivery drivers compensation',
            ],
            [
                'name' => 'Can Caps, Seals & Packaging',
                'slug' => 'caps-seals-packaging',
                'description' => 'Hygienic dust-proof shrink seals, tamper-evident caps, labels',
            ],
            [
                'name' => 'RO Plant Chemicals & Filters',
                'slug' => 'plant-consumables',
                'description' => 'Membranes, sediment filters, activated carbon, and sanitizers',
            ],
            [
                'name' => 'Office & Miscellaneous',
                'slug' => 'miscellaneous',
                'description' => 'Stationery, tea/refreshments, administrative overheads',
            ],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
