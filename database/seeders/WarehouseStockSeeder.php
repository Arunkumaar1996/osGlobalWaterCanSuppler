<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

class WarehouseStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            return;
        }

        $admin = User::where('mobile', '9876543210')->first();

        $stockConfigs = [
            'CAN-20L-RO' => ['full' => 150, 'empty' => 60, 'cleaning' => 30, 'damaged' => 5, 'reorder' => 40],
            'CAN-10L-RO' => ['full' => 80, 'empty' => 25, 'cleaning' => 15, 'damaged' => 2, 'reorder' => 20],
            'CAN-5L-PET' => ['full' => 50, 'empty' => 10, 'cleaning' => 10, 'damaged' => 1, 'reorder' => 15],
            'ACC-PUMP-01' => ['full' => 45, 'empty' => 0, 'cleaning' => 0, 'damaged' => 0, 'reorder' => 10],
        ];

        foreach ($stockConfigs as $sku => $cfg) {
            $product = Product::where('sku', $sku)->first();
            if (!$product) {
                continue;
            }

            $stock = WarehouseStock::updateOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ],
                [
                    'can_type_id' => $product->can_type_id,
                    'full_stock' => $cfg['full'],
                    'empty_stock' => $cfg['empty'],
                    'under_cleaning' => $cfg['cleaning'],
                    'damaged_stock' => $cfg['damaged'],
                    'reserved_stock' => 0,
                    'reorder_level' => $cfg['reorder'],
                ]
            );

            // Log initial stock audit entries
            InventoryMovement::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'can_type_id' => $product->can_type_id,
                'user_id' => $admin?->id,
                'movement_type' => 'stock_audit_adjustment',
                'state_from' => 'none',
                'state_to' => 'full',
                'quantity' => $cfg['full'],
                'full_stock_after' => $stock->full_stock,
                'empty_stock_after' => $stock->empty_stock,
                'under_cleaning_after' => $stock->under_cleaning,
                'damaged_stock_after' => $stock->damaged_stock,
                'reserved_stock_after' => 0,
                'notes' => 'Baseline physical stock audit intake',
                'created_at' => now(),
            ]);
        }
    }
}
