<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create a new product and handle slug generation
     */
    public function createProduct(array $data, User $admin): Product
    {
        return DB::transaction(function () use ($data, $admin) {
            $slug = Str::slug($data['name']);
            $count = Product::where('slug', 'LIKE', "{$slug}%")->count();
            if ($count > 0) {
                $slug = "{$slug}-" . ($count + 1);
            }

            $product = Product::create([
                'category_id' => $data['category_id'],
                'can_type_id' => $data['can_type_id'] ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'sku' => strtoupper($data['sku']),
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'capacity_value' => $data['capacity_value'],
                'capacity_unit' => $data['capacity_unit'] ?? 'Litre',
                'selling_price' => $data['selling_price'],
                'tax_rate_percent' => $data['tax_rate_percent'] ?? 18.00,
                'is_tax_inclusive' => $data['is_tax_inclusive'] ?? true,
                'min_order_quantity' => $data['min_order_quantity'] ?? 1,
                'max_order_quantity' => $data['max_order_quantity'] ?? 50,
                'requires_can_exchange' => $data['requires_can_exchange'] ?? true,
                'is_featured' => $data['is_featured'] ?? false,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Initial price history entry
            ProductPriceHistory::create([
                'product_id' => $product->id,
                'old_price' => 0.00,
                'new_price' => $product->selling_price,
                'changed_by_user_id' => $admin->id,
                'reason' => 'Initial product catalog creation',
                'effective_from' => now(),
                'created_at' => now(),
            ]);

            return $product;
        });
    }

    /**
     * Update product and log price change history if selling price is modified
     */
    public function updateProduct(Product $product, array $data, User $admin, ?string $reason = null): Product
    {
        return DB::transaction(function () use ($product, $data, $admin, $reason) {
            $oldPrice = (float) $product->selling_price;
            $newPrice = isset($data['selling_price']) ? (float) $data['selling_price'] : $oldPrice;

            if (abs($oldPrice - $newPrice) > 0.001) {
                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'changed_by_user_id' => $admin->id,
                    'reason' => $reason ?: 'Administrative price update',
                    'effective_from' => now(),
                    'created_at' => now(),
                ]);
            }

            $product->update($data);

            return $product;
        });
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus(Product $product): bool
    {
        $product->update(['is_active' => ! $product->is_active]);
        return $product->is_active;
    }
}
