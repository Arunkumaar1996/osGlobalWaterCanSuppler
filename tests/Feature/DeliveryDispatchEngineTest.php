<?php

namespace Tests\Feature;

use App\Models\CanType;
use App\Models\CustomerAddress;
use App\Models\DeliveryPartner;
use App\Models\DeliverySlot;
use App\Models\DeliverySlotCapacity;
use App\Models\DeliveryTrip;
use App\Models\DeliveryTripStop;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryDispatchEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_delivery_fleet_overview(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.delivery.index'));

        $response->assertStatus(200);
        $response->assertSee('Delivery Dispatch');
        $response->assertSee('Scheduled Runsheets');
        $response->assertSee('Unassigned Orders Waiting for Dispatch');
    }

    public function test_admin_can_register_third_party_delivery_partner(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->post(route('admin.delivery.partners.store'), [
            'name' => 'Shadowfax Express',
            'contact_person' => 'Rajesh Kumar',
            'contact_phone' => '9888877777',
            'contact_email' => 'operations@shadowfax.mock',
            'billing_type' => 'per_order',
            'rate_per_unit' => 38.50,
            'notes' => 'Quick intra-city delivery fleet contract',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('delivery_partners', [
            'name' => 'Shadowfax Express',
            'contact_person' => 'Rajesh Kumar',
            'billing_type' => 'per_order',
            'rate_per_unit' => 38.50,
        ]);
    }

    public function test_admin_can_create_delivery_trip_for_staff_driver_and_assign_order_with_otp(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $staff = StaffProfile::first();
        $slot = DeliverySlot::first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $product = Product::first();

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // Create an unassigned order
        $order = Order::create([
            'order_number' => 'ORD-TRIP-001',
            'idempotency_key' => 'IDEM-TRIP-001',
            'customer_profile_id' => $customerUser->customerProfile->id,
            'branch_id' => $staff->branch_id,
            'snapshot_address' => ['contact_name' => 'John', 'contact_mobile' => '9876543213', 'area' => 'Anna Nagar', 'pincode' => '600001'],
            'order_status' => 'confirmed',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal_amount' => 160.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'total_amount' => 160.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => $tomorrow,
            'delivery_slot_id' => $slot->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'quantity' => 2,
            'unit_price' => 80.00,
            'tax_rate_percent' => 0,
            'tax_amount' => 0,
            'subtotal' => 160.00,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.delivery.trips.store'), [
            'trip_date' => $tomorrow,
            'delivery_slot_id' => $slot->id,
            'driver_type' => 'staff',
            'staff_profile_id' => $staff->id,
            'vehicle_number' => 'TN-01-XY-5678',
            'notes' => 'Morning route priority deliveries',
            'order_ids' => [$order->id],
        ]);

        $trip = DeliveryTrip::latest('id')->first();
        $this->assertNotNull($trip);
        $response->assertRedirect(route('admin.delivery.trips.show', $trip->id));

        $this->assertEquals('scheduled', $trip->status);
        $this->assertEquals(1, $trip->total_orders_count);

        // Verify stop was created with 4-digit OTP
        $stop = DeliveryTripStop::where('delivery_trip_id', $trip->id)->first();
        $this->assertNotNull($stop);
        $this->assertEquals($order->id, $stop->order_id);
        $this->assertEquals(4, strlen($stop->delivery_otp));
        $this->assertEquals('pending', $stop->status);

        // Verify order delivery_status updated to assigned
        $order->refresh();
        $this->assertEquals('assigned', $order->delivery_status);
    }

    public function test_admin_can_create_delivery_trip_for_third_party_partner(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $slot = DeliverySlot::first();

        $partner = DeliveryPartner::create([
            'branch_id' => 1,
            'name' => 'Porter Express Fleet',
            'contact_person' => 'Vignesh',
            'contact_phone' => '9944332211',
            'billing_type' => 'per_order',
            'rate_per_unit' => 40.00,
            'is_active' => true,
        ]);

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('admin.delivery.trips.store'), [
            'trip_date' => $tomorrow,
            'delivery_slot_id' => $slot->id,
            'driver_type' => 'partner',
            'delivery_partner_id' => $partner->id,
            'vehicle_number' => 'TN-09-AB-9999',
            'notes' => 'Outsourced peak load partner',
        ]);

        $trip = DeliveryTrip::latest('id')->first();
        $this->assertNotNull($trip);
        $this->assertEquals('partner', $trip->driver_type);
        $this->assertEquals('Porter Express Fleet', $trip->driver_name);
    }

    public function test_dispatching_trip_updates_trip_in_transit_and_orders_out_for_delivery(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $staff = StaffProfile::first();
        $slot = DeliverySlot::first();
        $customerUser = User::where('mobile', '9876543213')->first();

        $order = Order::create([
            'order_number' => 'ORD-TRIP-002',
            'idempotency_key' => 'IDEM-TRIP-002',
            'customer_profile_id' => $customerUser->customerProfile->id,
            'branch_id' => 1,
            'snapshot_address' => ['city' => 'Chennai'],
            'order_status' => 'confirmed',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal_amount' => 100.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'total_amount' => 100.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => Carbon::today(),
            'delivery_slot_id' => $slot->id,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TEST-0002',
            'branch_id' => 1,
            'delivery_slot_id' => $slot->id,
            'trip_date' => Carbon::today(),
            'driver_type' => 'staff',
            'staff_profile_id' => $staff->id,
            'status' => 'scheduled',
        ]);

        DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'status' => 'pending',
            'delivery_otp' => '5432',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.delivery.trips.dispatch', $trip->id));
        $response->assertSessionHas('success');

        $trip->refresh();
        $this->assertEquals('in_transit', $trip->status);
        $this->assertNotNull($trip->started_at);

        $order->refresh();
        $this->assertEquals('out_for_delivery', $order->delivery_status);
        $this->assertEquals('out_for_delivery', $order->order_status);
    }

    public function test_verifying_delivery_otp_completes_stop_and_records_can_exchange_and_cod_payment(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $staff = StaffProfile::first();
        $slot = DeliverySlot::first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $product = Product::first();

        $order = Order::create([
            'order_number' => 'ORD-TRIP-003',
            'idempotency_key' => 'IDEM-TRIP-003',
            'customer_profile_id' => $customerUser->customerProfile->id,
            'branch_id' => 1,
            'snapshot_address' => ['city' => 'Chennai'],
            'order_status' => 'out_for_delivery',
            'fulfillment_status' => 'fulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'out_for_delivery',
            'subtotal_amount' => 160.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'total_amount' => 160.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => Carbon::today(),
            'delivery_slot_id' => $slot->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'can_type_id' => $product->can_type_id,
            'quantity' => 2,
            'unit_price' => 80.00,
            'tax_rate_percent' => 0,
            'tax_amount' => 0,
            'subtotal' => 160.00,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TEST-0003',
            'branch_id' => 1,
            'delivery_slot_id' => $slot->id,
            'trip_date' => Carbon::today(),
            'driver_type' => 'staff',
            'staff_profile_id' => $staff->id,
            'status' => 'in_transit',
        ]);

        $stop = DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'status' => 'pending',
            'delivery_otp' => '7890',
        ]);

        // Complete stop with valid OTP, 2 empty cans exchanged, and ₹160 COD collected
        $response = $this->actingAs($admin)->post(route('admin.delivery.stops.complete', $stop->id), [
            'otp' => '7890',
            'empty_cans_collected' => 2,
            'cash_collected' => 160.00,
            'notes' => 'Customer paid in exact cash; handed over 2 empty cans',
        ]);

        $response->assertSessionHas('success');

        // Verify stop marked delivered
        $stop->refresh();
        $this->assertEquals('delivered', $stop->status);
        $this->assertNotNull($stop->otp_verified_at);

        // Verify order marked delivered and paid
        $order->refresh();
        $this->assertEquals('delivered', $order->delivery_status);
        $this->assertEquals('delivered', $order->order_status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals(160.00, (float) $order->paid_amount);

        // Verify Empty Can exchange record created
        $this->assertDatabaseHas('can_movements', [
            'customer_profile_id' => $customerUser->customerProfile->id,
            'movement_type' => 'delivery_exchange',
            'cans_delivered_full' => 2,
            'cans_collected_empty' => 2,
        ]);

        // Verify Payment receipt created for cash collection
        $this->assertDatabaseHas('payments', [
            'customer_profile_id' => $customerUser->customerProfile->id,
            'order_id' => $order->id,
            'amount' => 160.00,
            'payment_method' => 'cash',
            'status' => 'successful',
        ]);
    }

    public function test_entering_incorrect_delivery_otp_fails_verification(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $slot = DeliverySlot::first();
        $customerUser = User::where('mobile', '9876543213')->first();

        $order = Order::create([
            'order_number' => 'ORD-TRIP-004',
            'idempotency_key' => 'IDEM-TRIP-004',
            'customer_profile_id' => $customerUser->customerProfile->id,
            'branch_id' => 1,
            'snapshot_address' => ['city' => 'Chennai'],
            'order_status' => 'out_for_delivery',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'out_for_delivery',
            'subtotal_amount' => 100.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'total_amount' => 100.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => Carbon::today(),
            'delivery_slot_id' => $slot->id,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TEST-0004',
            'branch_id' => 1,
            'delivery_slot_id' => $slot->id,
            'trip_date' => Carbon::today(),
            'driver_type' => 'staff',
            'status' => 'in_transit',
        ]);

        $stop = DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'status' => 'pending',
            'delivery_otp' => '7890',
        ]);

        // Try with wrong OTP
        $response = $this->actingAs($admin)->post(route('admin.delivery.stops.complete', $stop->id), [
            'otp' => '1111',
            'empty_cans_collected' => 0,
            'cash_collected' => 0,
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertEquals('pending', $stop->fresh()->status);
    }

    public function test_driver_reporting_failed_stop_updates_status(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $slot = DeliverySlot::first();
        $customerUser = User::where('mobile', '9876543213')->first();

        $order = Order::create([
            'order_number' => 'ORD-TRIP-005',
            'idempotency_key' => 'IDEM-TRIP-005',
            'customer_profile_id' => $customerUser->customerProfile->id,
            'branch_id' => 1,
            'snapshot_address' => ['city' => 'Chennai'],
            'order_status' => 'out_for_delivery',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'out_for_delivery',
            'subtotal_amount' => 100.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'total_amount' => 100.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => Carbon::today(),
            'delivery_slot_id' => $slot->id,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TEST-0005',
            'branch_id' => 1,
            'delivery_slot_id' => $slot->id,
            'trip_date' => Carbon::today(),
            'driver_type' => 'staff',
            'status' => 'in_transit',
        ]);

        $stop = DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'status' => 'pending',
            'delivery_otp' => '9988',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.delivery.stops.fail', $stop->id), [
            'failure_reason' => 'Customer flat locked; phone switched off',
            'reschedule' => false,
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('failed', $stop->fresh()->status);
        $this->assertEquals('failed', $order->fresh()->delivery_status);
    }

    public function test_admin_can_view_delivery_slots_and_toggle_block(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $slot = DeliverySlot::first();
        $date = Carbon::tomorrow()->format('Y-m-d');

        $response = $this->actingAs($admin)->get(route('admin.delivery.slots.index', ['date' => $date]));

        $response->assertStatus(200);
        $response->assertSee('Delivery Slots &amp; Fleet Capacity Engine', false);
        $response->assertSee($slot->name);

        // Toggle block on slot
        $toggleResp = $this->actingAs($admin)->post(route('admin.delivery.slots.toggle-block', $slot->id), [
            'date' => $date,
        ]);
        $toggleResp->assertSessionHas('success');

        $capacity = DeliverySlotCapacity::where('delivery_slot_id', $slot->id)->first();
        $this->assertNotNull($capacity);
        $this->assertTrue((bool) $capacity->is_blocked);
    }

    public function test_completing_delivery_trip_marks_completed(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $slot = DeliverySlot::first();

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TEST-0006',
            'branch_id' => 1,
            'delivery_slot_id' => $slot->id,
            'trip_date' => Carbon::today(),
            'driver_type' => 'staff',
            'status' => 'in_transit',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.delivery.trips.complete', $trip->id));
        $response->assertSessionHas('success');

        $trip->refresh();
        $this->assertEquals('completed', $trip->status);
        $this->assertNotNull($trip->completed_at);
    }
}
