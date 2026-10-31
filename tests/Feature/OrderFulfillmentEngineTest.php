<?php

namespace Tests\Feature;

use App\Models\CustomerProfile;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\FulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFulfillmentEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Product $product;
    protected WarehouseStock $stock;
    protected Order $order;
    protected OrderItem $orderItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('mobile', '9876543210')->first();
        $this->customer = User::where('mobile', '9876543213')->first();
        $this->warehouse = Warehouse::first();
        $this->supplier = Supplier::first();
        $this->product = Product::first();

        // Ensure warehouse stock has 100 available cans
        $this->stock = WarehouseStock::updateOrCreate(
            ['warehouse_id' => $this->warehouse->id, 'product_id' => $this->product->id],
            ['full_stock' => 100, 'empty_stock' => 20, 'under_cleaning' => 10, 'damaged_stock' => 0, 'reserved_stock' => 0]
        );

        // Create a test customer order of 10 cans
        $custProfile = CustomerProfile::where('user_id', $this->customer->id)->first();
        $slot = DeliverySlot::first();

        $this->order = Order::create([
            'order_number' => 'ORD-TEST-1001',
            'idempotency_key' => 'idem-test-1001',
            'customer_profile_id' => $custProfile->id,
            'branch_id' => $this->warehouse->branch_id,
            'delivery_address_id' => $custProfile->addresses->first()?->id,
            'snapshot_address' => [
                'address_line1' => 'Flat 402, Lotus Towers',
                'area' => 'Guindy',
                'city' => 'Chennai',
                'pincode' => '600032',
                'landmark' => 'Near Metro Station',
            ],
            'order_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal_amount' => 800.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'discount_amount' => 0.00,
            'total_amount' => 800.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => now()->addDay(),
            'delivery_slot_id' => $slot->id,
        ]);

        $this->orderItem = $this->order->items()->create([
            'product_id' => $this->product->id,
            'product_name_snapshot' => $this->product->name,
            'can_type_id' => $this->product->can_type_id,
            'quantity' => 10,
            'fulfilled_quantity' => 0,
            'delivered_quantity' => 0,
            'unit_price' => 80.00,
            'tax_rate_percent' => 0.00,
            'tax_amount' => 0.00,
            'subtotal' => 800.00,
        ]);
    }

    public function test_admin_can_view_orders_list_with_independent_status_badges(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Orders &amp; Dispatches', false);
        $response->assertSee($this->order->order_number);
        $response->assertSee('Unfulfilled');
    }

    public function test_customer_cannot_access_admin_orders_management(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.orders.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_allocate_fulfillment_from_warehouse_and_stock_is_reserved(): void
    {
        $initialAvailable = $this->stock->available_stock;
        $initialReserved = $this->stock->reserved_stock;

        // Allocate 6 cans from Warehouse Stock
        $response = $this->actingAs($this->admin)->post(route('admin.orders.fulfillments.store', $this->order), [
            'source_type' => 'warehouse',
            'source_id' => $this->warehouse->id,
            'notes' => 'Allocated from Guindy central stock',
            'items' => [
                [
                    'order_item_id' => $this->orderItem->id,
                    'quantity' => 6,
                ],
            ],
        ]);

        $response->assertRedirect();

        // 1. Verify fulfillment created
        $this->assertDatabaseHas('order_fulfillments', [
            'order_id' => $this->order->id,
            'source_type' => 'warehouse',
            'warehouse_id' => $this->warehouse->id,
            'status' => 'allocated',
        ]);

        // 2. Verify stock reserved
        $this->stock->refresh();
        $this->assertEquals($initialReserved + 6, $this->stock->reserved_stock);
        $this->assertEquals($initialAvailable - 6, $this->stock->available_stock);

        // 3. Verify order item fulfilled_quantity updated
        $this->orderItem->refresh();
        $this->assertEquals(6, $this->orderItem->fulfilled_quantity);
        $this->assertEquals(4, $this->orderItem->unfulfilled_quantity);

        // 4. Verify Order fulfillment_status is partially_fulfilled
        $this->order->refresh();
        $this->assertEquals('partially_fulfilled', $this->order->fulfillment_status);
        $this->assertEquals('confirmed', $this->order->order_status);
    }

    public function test_admin_cannot_allocate_more_warehouse_stock_than_available(): void
    {
        // Try to allocate 200 cans when only 100 available
        $this->orderItem->update(['quantity' => 200]);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.fulfillments.store', $this->order), [
            'source_type' => 'warehouse',
            'source_id' => $this->warehouse->id,
            'items' => [
                [
                    'order_item_id' => $this->orderItem->id,
                    'quantity' => 150,
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('order_fulfillments', ['order_id' => $this->order->id]);
    }

    public function test_admin_can_allocate_split_fulfillment_partially_from_warehouse_and_partially_from_supplier(): void
    {
        $service = app(FulfillmentService::class);

        // 1. Allocate 6 cans from Warehouse
        $ful1 = $service->allocateFulfillment(
            order: $this->order,
            sourceType: 'warehouse',
            sourceId: $this->warehouse->id,
            allocations: [['order_item_id' => $this->orderItem->id, 'quantity' => 6]],
            user: $this->admin,
            notes: 'Batch from warehouse'
        );

        $this->order->refresh();
        $this->assertEquals('partially_fulfilled', $this->order->fulfillment_status);

        // 2. Allocate remaining 4 cans from Supplier
        $ful2 = $service->allocateFulfillment(
            order: $this->order,
            sourceType: 'supplier',
            sourceId: $this->supplier->id,
            allocations: [['order_item_id' => $this->orderItem->id, 'quantity' => 4]],
            user: $this->admin,
            notes: 'Dropship from supplier'
        );

        $this->order->refresh();
        $this->orderItem->refresh();

        // 3. Verify total fulfilled = 10 (order is fully fulfilled)
        $this->assertEquals(10, $this->orderItem->fulfilled_quantity);
        $this->assertEquals(0, $this->orderItem->unfulfilled_quantity);
        $this->assertEquals('fulfilled', $this->order->fulfillment_status);

        $this->assertEquals(2, $this->order->fulfillments()->count());
    }

    public function test_cancelling_fulfillment_releases_reserved_stock_and_reverses_status(): void
    {
        $service = app(FulfillmentService::class);

        $fulfillment = $service->allocateFulfillment(
            order: $this->order,
            sourceType: 'warehouse',
            sourceId: $this->warehouse->id,
            allocations: [['order_item_id' => $this->orderItem->id, 'quantity' => 10]],
            user: $this->admin
        );

        $this->order->refresh();
        $this->assertEquals('fulfilled', $this->order->fulfillment_status);

        $initialReserved = $this->stock->fresh()->reserved_stock;

        // Cancel fulfillment
        $response = $this->actingAs($this->admin)->post(route('admin.fulfillments.cancel', $fulfillment), [
            'reason' => 'Truck breakdown at origin warehouse',
        ]);

        $response->assertRedirect();

        // Verify reserved stock released
        $this->stock->refresh();
        $this->assertEquals($initialReserved - 10, $this->stock->reserved_stock);

        // Verify order reversed to unfulfilled
        $this->order->refresh();
        $this->assertEquals('unfulfilled', $this->order->fulfillment_status);

        $fulfillment->refresh();
        $this->assertEquals('cancelled', $fulfillment->status);
    }

    public function test_dispatching_warehouse_fulfillment_deducts_physical_full_stock(): void
    {
        $service = app(FulfillmentService::class);

        $fulfillment = $service->allocateFulfillment(
            order: $this->order,
            sourceType: 'warehouse',
            sourceId: $this->warehouse->id,
            allocations: [['order_item_id' => $this->orderItem->id, 'quantity' => 10]],
            user: $this->admin
        );

        $initialFull = $this->stock->fresh()->full_stock;
        $initialReserved = $this->stock->fresh()->reserved_stock;

        // Dispatch
        $response = $this->actingAs($this->admin)->post(route('admin.fulfillments.dispatch', $fulfillment));

        $response->assertRedirect();

        $this->stock->refresh();
        // Both full stock and reserved stock decreased by 10
        $this->assertEquals($initialFull - 10, $this->stock->full_stock);
        $this->assertEquals($initialReserved - 10, $this->stock->reserved_stock);

        $fulfillment->refresh();
        $this->assertEquals('dispatched', $fulfillment->status);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'order_dispatched',
            'quantity' => 10,
        ]);
    }

    public function test_admin_can_generate_batch_dispatch_manifest(): void
    {
        $service = app(FulfillmentService::class);

        $fulfillment = $service->allocateFulfillment(
            order: $this->order,
            sourceType: 'warehouse',
            sourceId: $this->warehouse->id,
            allocations: [['order_item_id' => $this->orderItem->id, 'quantity' => 10]],
            user: $this->admin
        );

        $response = $this->actingAs($this->admin)->post(route('admin.manifests.store'), [
            'branch_id' => $this->warehouse->branch_id,
            'warehouse_id' => $this->warehouse->id,
            'vehicle_number' => 'TN-04-AB-1234',
            'driver_name' => 'K. Murugan',
            'driver_phone' => '9840011223',
            'notes' => 'Morning dispatch sector 1',
            'fulfillments' => [$fulfillment->id],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('dispatch_manifests', [
            'vehicle_number' => 'TN-04-AB-1234',
            'driver_name' => 'K. Murugan',
            'status' => 'generated',
        ]);

        $fulfillment->refresh();
        $this->assertEquals('ready_for_dispatch', $fulfillment->status);
    }

    public function test_admin_can_cancel_order_completely_and_release_all_reservations(): void
    {
        $service = app(FulfillmentService::class);

        // Allocate fulfillment
        $service->allocateFulfillment(
            order: $this->order,
            sourceType: 'warehouse',
            sourceId: $this->warehouse->id,
            allocations: [['order_item_id' => $this->orderItem->id, 'quantity' => 10]],
            user: $this->admin
        );

        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $this->order), [
            'reason' => 'Customer called to cancel delivery',
        ]);

        $response->assertRedirect();

        $this->order->refresh();
        $this->assertEquals('cancelled', $this->order->order_status);
        $this->assertEquals('unfulfilled', $this->order->fulfillment_status);
        $this->assertEquals('Customer called to cancel delivery', $this->order->cancellation_reason);

        // Stock reservation released
        $this->stock->refresh();
        $this->assertEquals(0, $this->stock->reserved_stock);
    }

    public function test_admin_can_update_independent_status_on_order(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.orders.update-status', $this->order), [
            'status_type' => 'delivery_status',
            'new_status' => 'out_for_delivery',
            'comment' => 'Driver loaded van and left facility',
        ]);

        $response->assertRedirect();

        $this->order->refresh();
        $this->assertEquals('out_for_delivery', $this->order->delivery_status);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $this->order->id,
            'to_status' => 'out_for_delivery',
            'comment' => 'Driver loaded van and left facility (delivery_status)',
        ]);
    }
}
