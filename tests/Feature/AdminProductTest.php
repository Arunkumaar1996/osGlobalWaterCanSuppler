<?php

namespace Tests\Feature;

use App\Models\CanType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPriceHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('mobile', '9876543210')->first();
        $this->customer = User::where('mobile', '9876543213')->first();
    }

    public function test_admin_can_view_product_catalog_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Product Catalog', false);
        $response->assertSee('20 Litre Premium Water Can');
    }

    public function test_customer_cannot_access_admin_product_routes(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.products.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_product_and_initial_price_history_is_logged(): void
    {
        $category = ProductCategory::first();
        $canType = CanType::first();

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Premium Alkaline 20L Can',
            'sku' => 'ALK-20L-PREM',
            'category_id' => $category->id,
            'can_type_id' => $canType->id,
            'short_description' => 'High pH mineral water',
            'description' => 'Alkaline drinking water enriched with minerals',
            'capacity_value' => 20,
            'capacity_unit' => 'Litre',
            'selling_price' => 120.00,
            'tax_rate_percent' => 18.00,
            'is_tax_inclusive' => 1,
            'min_order_quantity' => 1,
            'max_order_quantity' => 25,
            'requires_can_exchange' => 1,
            'is_featured' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'sku' => 'ALK-20L-PREM',
            'selling_price' => 120.00,
            'is_active' => 1,
        ]);

        $product = Product::where('sku', 'ALK-20L-PREM')->first();

        $this->assertDatabaseHas('product_price_histories', [
            'product_id' => $product->id,
            'old_price' => 0.00,
            'new_price' => 120.00,
            'changed_by_user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_update_product_and_price_change_is_audited(): void
    {
        $product = Product::first();
        $oldPrice = (float) $product->selling_price;
        $newPrice = $oldPrice + 10.00;

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'sku' => $product->sku,
            'category_id' => $product->category_id,
            'can_type_id' => $product->can_type_id,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'capacity_value' => $product->capacity_value,
            'capacity_unit' => $product->capacity_unit,
            'selling_price' => $newPrice,
            'tax_rate_percent' => $product->tax_rate_percent,
            'is_tax_inclusive' => 1,
            'min_order_quantity' => $product->min_order_quantity,
            'max_order_quantity' => $product->max_order_quantity,
            'requires_can_exchange' => 1,
            'price_change_reason' => 'Quarterly inflation review',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'selling_price' => $newPrice,
        ]);

        $this->assertDatabaseHas('product_price_histories', [
            'product_id' => $product->id,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'reason' => 'Quarterly inflation review',
            'changed_by_user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $product = Product::first();
        $initialStatus = $product->is_active;

        $response = $this->actingAs($this->admin)->patch(route('admin.products.toggle-status', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => !$initialStatus,
        ]);
    }

    public function test_admin_can_view_price_history_page(): void
    {
        $product = Product::first();

        // Create a price history entry
        ProductPriceHistory::create([
            'product_id' => $product->id,
            'old_price' => 70.00,
            'new_price' => 80.00,
            'changed_by_user_id' => $this->admin->id,
            'reason' => 'Fuel cost surcharge',
            'effective_from' => now(),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.price-history', $product));

        $response->assertStatus(200);
        $response->assertSee('Price Audit Trail');
        $response->assertSee('Fuel cost surcharge');
    }
}
