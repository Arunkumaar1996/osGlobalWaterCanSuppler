<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_view_cart_page(): void
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Your Water Cart');
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $product = Product::first();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect('/cart');
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_can_update_quantity_in_cart(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $product = Product::first();

        $this->actingAs($customer)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $item = \App\Models\CartItem::where('product_id', $product->id)->first();

        $response = $this->actingAs($customer)->post("/cart/update/{$item->id}", [
            'quantity' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $product = Product::first();

        $this->actingAs($customer)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $item = \App\Models\CartItem::where('product_id', $product->id)->first();

        $response = $this->actingAs($customer)->post("/cart/remove/{$item->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }

    public function test_user_can_toggle_can_exchange_mode_for_new_customer(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $product = Product::first();

        $this->actingAs($customer)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        // Toggle to new customer mode (no empty cans to return, adds deposit)
        $response = $this->actingAs($customer)->post('/cart/exchange-mode', [
            'has_empty_cans_to_exchange' => 0,
        ]);

        $response->assertRedirect('/cart');
        $cart = \App\Models\Cart::where('user_id', $customer->id)->first();
        $this->assertFalse((bool) $cart->has_empty_cans_to_exchange);
        $this->assertEquals(300.00, $cart->calculateDepositAmount()); // 2 cans * 150 deposit
    }
}
