<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            BusinessAndBranchSeeder::class,
            UserSeeder::class,
            ProductAndCmsSeeder::class,
            DeliverySlotSeeder::class,
            SupplierSeeder::class,
            WarehouseStockSeeder::class,
            ExpenseCategorySeeder::class,
        ]);
    }
}
