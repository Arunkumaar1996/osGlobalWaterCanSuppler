<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CanType;
use App\Models\CustomerAddress;
use App\Models\DeliveryPincode;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndWaterCanOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Product $product20L;
    protected Branch $branch;
    protected DeliverySlot $slot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::where('mobile', '9876543213')->first();
        $this->admin = User::where('mobile', '9876543210')->first();
        $this->branch = Branch::first();
        $this->product20L = Product::where('capacity_value', 20)->first();
        $this->slot = DeliverySlot::first();
    }

    public function test_user_can_add_water_can_to_cart_from_homepage(): void
    {
        $response = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product20L->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        // Check cart page
        $cartPage = $this->actingAs($this->customer)->get(route('cart.index'));
        $cartPage->assertStatus(200);
        $cartPage->assertSee('Your Water Cart');
        $cartPage->assertSee($this->product20L->name);
        $cartPage->assertSee('40 Litres');
    }

    public function test_buy_now_adds_to_cart_and_redirects_to_checkout(): void
    {
        $response = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product20L->id,
            'quantity' => 1,
            'buy_now' => 1,
        ]);

        $response->assertRedirect(route('checkout.index'));
    }

    public function test_customer_can_add_new_address_at_checkout(): void
    {
        $response = $this->actingAs($this->customer)->post(route('checkout.address.store'), [
            'contact_person_name' => 'Anand K',
            'contact_person_mobile' => '9876543213',
            'address_type' => 'home',
            'house_flat_no' => 'Flat 5A',
            'building_apartment_name' => 'Green Towers',
            'street_address' => '10, 4th Cross Street',
            'area' => 'Guindy',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600032',
            'delivery_instructions' => 'Ring twice',
            'is_default' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customer_addresses', [
            'contact_person_name' => 'Anand K',
            'pincode' => '600032',
            'house_flat_no' => 'Flat 5A',
        ]);
    }

    public function test_end_to_end_frontend_order_placement_and_backend_fulfillment(): void
    {
        // 1. Customer adds 2 water cans to cart
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product20L->id,
            'quantity' => 2,
        ]);

        // 2. Customer opens checkout
        $checkoutPage = $this->actingAs($this->customer)->get(route('checkout.index'));
        $checkoutPage->assertStatus(200);
        $checkoutPage->assertSee('Order Checkout');

        // 3. Process order with tomorrow date
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $address = CustomerAddress::where('customer_profile_id', $this->customer->customerProfile->id)->first();

        $processResponse = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'delivery_address_id' => $address->id,
            'delivery_date' => $tomorrow,
            'delivery_slot_id' => $this->slot->id,
            'payment_method' => 'cod',
            'customer_notes' => 'Please deliver on time.',
        ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $processResponse->assertRedirect(route('customer.orders.show', $order->id));

        $this->assertEquals('confirmed', $order->order_status);
        $this->assertEquals('unfulfilled', $order->fulfillment_status);
        $this->assertEquals('pending', $order->delivery_status);

        // 4. Customer dashboard displays recent order
        $dashboard = $this->actingAs($this->customer)->get(route('customer.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee($order->order_number);
        $dashboard->assertSee('Recent Deliveries & Orders', false);

        // 5. Admin views order in backend
        $adminOrderPage = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));
        $adminOrderPage->assertStatus(200);
        $adminOrderPage->assertSee($order->order_number);
        $adminOrderPage->assertSee('Guindy');

        // 6. Admin allocates fulfillment from warehouse
        $warehouse = Warehouse::where('branch_id', $order->branch_id)->first();
        $orderItem = $order->items->first();

        $allocateResponse = $this->actingAs($this->admin)->post(route('admin.orders.fulfillments.store', $order), [
            'source_type' => 'warehouse',
            'source_id' => $warehouse->id,
            'items' => [
                ['order_item_id' => $orderItem->id, 'quantity' => 2],
            ],
            'notes' => 'Fulfill from Guindy Hub',
        ]);

        $allocateResponse->assertRedirect();
        $allocateResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('fulfilled', $order->fulfillment_status);

        $fulfillment = $order->fulfillments->first();
        $this->assertNotNull($fulfillment);
        $this->assertEquals('allocated', $fulfillment->status);

        // 7. Admin dispatches fulfillment
        $dispatchResponse = $this->actingAs($this->admin)->post(route('admin.fulfillments.dispatch', $fulfillment));
        $dispatchResponse->assertRedirect();
        $dispatchResponse->assertSessionHas('success');

        $fulfillment->refresh();
        $this->assertEquals('dispatched', $fulfillment->status);
        $this->assertEquals('out_for_delivery', $order->fresh()->delivery_status);

        // 8. Customer order tracking page shows "Out for Delivery"
        $customerTracking = $this->actingAs($this->customer)->get(route('customer.orders.show', $order->id));
        $customerTracking->assertStatus(200);
        $customerTracking->assertSee('Out for Delivery');
    }
}
