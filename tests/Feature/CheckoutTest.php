<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_from_checkout_to_login(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/login');
    }

    public function test_customer_with_empty_cart_is_redirected_to_cart(): void
    {
        $customer = User::where('mobile', '9876543213')->first();

        $response = $this->actingAs($customer)->get('/checkout');

        $response->assertRedirect(route('cart.index'));
    }

    public function test_customer_can_view_checkout_with_cart_items(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $product = Product::first();

        $this->actingAs($customer)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($customer)->get('/checkout');

        $response->assertStatus(200);
        $response->assertSee('Order Checkout');
        $response->assertSee('Delivery Address');
        $response->assertSee('Delivery Date & Slot', false);
    }

    public function test_customer_can_place_cod_order_successfully(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $product = Product::where('slug', '20-litre-bubble-top-water-can')->first();
        $address = CustomerAddress::where('customer_profile_id', $customer->customerProfile->id)->first();
        $slot = DeliverySlot::first();

        // 1. Add to cart
        $this->actingAs($customer)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $deliveryDate = Carbon::today()->addDay()->format('Y-m-d');

        // 2. Process checkout
        $response = $this->actingAs($customer)->post('/checkout/process', [
            'delivery_address_id' => $address->id,
            'delivery_date' => $deliveryDate,
            'delivery_slot_id' => $slot->id,
            'payment_method' => 'cod',
            'customer_notes' => 'Please call upon arrival.',
        ]);

        $order = Order::where('customer_profile_id', $customer->customerProfile->id)->latest('id')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('customer.orders.show', $order->id));

        // 3. Verify Database records
        $this->assertEquals('confirmed', $order->order_status);
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals('cod', $order->payment_method_preferred);
        $this->assertEquals(160.00, (float) $order->subtotal_amount); // 2 x 80

        // 4. Verify Order Items created
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // 5. Verify Slot Capacity atomically reserved
        $this->assertDatabaseHas('delivery_slot_capacity', [
            'delivery_slot_id' => $slot->id,
            'reserved_orders_count' => 1,
            'reserved_litres_volume' => 40, // 2 x 20L
        ]);

        // 6. Verify Cart Cleared
        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_corporate_customer_can_order_using_pay_later_credit(): void
    {
        $corporate = User::where('mobile', '9876543214')->first();
        $product = Product::where('slug', '20-litre-bubble-top-water-can')->first();
        $address = CustomerAddress::where('customer_profile_id', $corporate->customerProfile->id)->first();
        $slot = DeliverySlot::first();

        $this->actingAs($corporate)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $deliveryDate = Carbon::today()->addDay()->format('Y-m-d');

        $response = $this->actingAs($corporate)->post('/checkout/process', [
            'delivery_address_id' => $address->id,
            'delivery_date' => $deliveryDate,
            'delivery_slot_id' => $slot->id,
            'payment_method' => 'pay_later',
        ]);

        $order = Order::where('customer_profile_id', $corporate->customerProfile->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('pay_later', $order->payment_method_preferred);
        $this->assertEquals('confirmed', $order->order_status);
    }

    public function test_customer_cannot_use_pay_later_if_not_approved(): void
    {
        $residential = User::where('mobile', '9876543213')->first(); // allow_pay_later is false by default
        $product = Product::first();
        $address = CustomerAddress::where('customer_profile_id', $residential->customerProfile->id)->first();
        $slot = DeliverySlot::first();

        $this->actingAs($residential)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($residential)->post('/checkout/process', [
            'delivery_address_id' => $address->id,
            'delivery_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'delivery_slot_id' => $slot->id,
            'payment_method' => 'pay_later',
        ]);

        $response->assertSessionHasErrors('checkout');
    }

    public function test_customer_can_view_order_details(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $product = Product::first();
        $address = CustomerAddress::where('customer_profile_id', $customer->customerProfile->id)->first();
        $slot = DeliverySlot::first();

        $this->actingAs($customer)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)->post('/checkout/process', [
            'delivery_address_id' => $address->id,
            'delivery_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'delivery_slot_id' => $slot->id,
            'payment_method' => 'cod',
        ]);

        $order = Order::where('customer_profile_id', $customer->customerProfile->id)->first();

        $response = $this->actingAs($customer)->get(route('customer.orders.show', $order->id));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Scheduled Delivery');
        $response->assertSee('Ordered Items');
    }

    public function test_new_customer_without_empty_cans_orders_with_can_deposit(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $product = Product::where('slug', '20-litre-bubble-top-water-can')->first();
        $address = CustomerAddress::where('customer_profile_id', $customer->customerProfile->id)->first();
        $slot = DeliverySlot::first();

        $this->actingAs($customer)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $deliveryDate = Carbon::today()->addDay()->format('Y-m-d');

        // Order as new customer (no empty cans to return)
        $response = $this->actingAs($customer)->post('/checkout/process', [
            'delivery_address_id' => $address->id,
            'delivery_date' => $deliveryDate,
            'delivery_slot_id' => $slot->id,
            'payment_method' => 'cod',
            'has_empty_cans_to_exchange' => 0,
        ]);

        $order = Order::where('customer_profile_id', $customer->customerProfile->id)->latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.orders.show', $order->id));

        $this->assertFalse((bool) $order->has_empty_cans_to_exchange);
        $this->assertEquals(0, $order->empty_cans_expected);
        $this->assertEquals(300.00, (float) $order->deposit_amount); // 2 cans x 150
        $this->assertEquals(460.00, (float) $order->total_amount); // 160 subtotal + 300 deposit

        // View order details page as customer
        $viewResponse = $this->actingAs($customer)->get(route('customer.orders.show', $order->id));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Refundable Can Deposit');
        $viewResponse->assertSee('New Customer');
    }
}
