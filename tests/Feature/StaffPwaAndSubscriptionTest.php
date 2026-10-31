<?php

namespace Tests\Feature;

use App\Models\CanType;
use App\Models\CustomerAddress;
use App\Models\DeliverySlot;
use App\Models\DeliveryTrip;
use App\Models\DeliveryTripStop;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StaffProfile;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPwaAndSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_staff_driver_can_view_assigned_deliveries_runsheet(): void
    {
        $staffUser = User::where('mobile', '9876543212')->first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $staff = $staffUser->staffProfile;
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();
        $product = Product::first();

        $today = Carbon::today()->format('Y-m-d');

        $order = Order::create([
            'order_number' => 'ORD-RUN-001',
            'idempotency_key' => 'IDEM-RUN-001',
            'customer_profile_id' => $customer->id,
            'branch_id' => $staff->branch_id,
            'delivery_address_id' => $address->id,
            'snapshot_address' => [
                'contact_name' => 'Kavitha R',
                'house_flat_no' => '4B',
                'street' => 'Anna Nagar Main Rd',
                'area' => 'Anna Nagar',
                'city' => 'Chennai',
                'pincode' => '600040',
            ],
            'order_status' => 'confirmed',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'assigned',
            'subtotal_amount' => 160.00,
            'tax_amount' => 8.00,
            'total_amount' => 168.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => $today,
            'delivery_slot_id' => $slot->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'can_type_id' => $product->can_type_id,
            'quantity' => 2,
            'delivered_quantity' => 0,
            'unit_price' => 80.00,
            'tax_rate_percent' => 5.00,
            'tax_amount' => 8.00,
            'subtotal' => 160.00,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TODAY-0001',
            'branch_id' => $staff->branch_id,
            'staff_profile_id' => $staff->id,
            'delivery_slot_id' => $slot->id,
            'trip_date' => $today,
            'driver_type' => 'staff',
            'vehicle_number' => 'TN-09-CB-4521',
            'status' => 'scheduled',
            'total_orders_count' => 1,
            'delivered_orders_count' => 0,
            'failed_orders_count' => 0,
            'total_litres' => 40,
            'total_cash_to_collect' => 168.00,
            'total_cash_collected' => 0.00,
            'total_empty_cans_returned' => 0,
        ]);

        $stop = DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'delivery_otp' => '4829',
            'status' => 'pending',
            'cash_to_collect' => 168.00,
            'empty_cans_expected' => 2,
            'empty_cans_collected' => 0,
        ]);

        $response = $this->actingAs($staffUser)->get(route('staff.deliveries'));

        $response->assertStatus(200);
        $response->assertSee('TRIP-TODAY-0001');
        $response->assertSee('ORD-RUN-001');
        $response->assertSee('Anna Nagar');
    }

    public function test_staff_driver_can_verify_and_complete_stop_with_valid_otp(): void
    {
        $staffUser = User::where('mobile', '9876543212')->first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $staff = $staffUser->staffProfile;
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();
        $product = Product::first();
        $today = Carbon::today()->format('Y-m-d');

        $order = Order::create([
            'order_number' => 'ORD-RUN-002',
            'idempotency_key' => 'IDEM-RUN-002',
            'customer_profile_id' => $customer->id,
            'branch_id' => $staff->branch_id,
            'delivery_address_id' => $address->id,
            'snapshot_address' => [
                'contact_name' => 'Kavitha R',
                'house_flat_no' => '4B',
                'street' => 'Anna Nagar',
                'area' => 'Anna Nagar',
                'city' => 'Chennai',
                'pincode' => '600040',
            ],
            'order_status' => 'confirmed',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'assigned',
            'subtotal_amount' => 160.00,
            'tax_amount' => 8.00,
            'total_amount' => 168.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => $today,
            'delivery_slot_id' => $slot->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'can_type_id' => $product->can_type_id,
            'quantity' => 2,
            'delivered_quantity' => 0,
            'unit_price' => 80.00,
            'tax_rate_percent' => 5.00,
            'tax_amount' => 8.00,
            'subtotal' => 160.00,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TODAY-0002',
            'branch_id' => $staff->branch_id,
            'staff_profile_id' => $staff->id,
            'delivery_slot_id' => $slot->id,
            'trip_date' => $today,
            'driver_type' => 'staff',
            'status' => 'in_transit',
            'total_orders_count' => 1,
            'delivered_orders_count' => 0,
            'failed_orders_count' => 0,
            'total_litres' => 40,
            'total_cash_to_collect' => 168.00,
            'total_cash_collected' => 0.00,
            'total_empty_cans_returned' => 0,
        ]);

        $stop = DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'delivery_otp' => '9142',
            'status' => 'pending',
            'cash_to_collect' => 168.00,
            'empty_cans_expected' => 2,
            'empty_cans_collected' => 0,
        ]);

        $response = $this->actingAs($staffUser)->post(route('staff.stops.verify', $stop), [
            'otp' => '9142',
            'empty_cans_collected' => 2,
            'cash_collected' => 168.00,
            'notes' => 'Handed over 2 cans and received 2 empties with exact cash.',
        ]);

        $response->assertSessionHas('success');

        $stop->refresh();
        $this->assertEquals('delivered', $stop->status);
        $this->assertEquals(2, $stop->empty_cans_collected);
        $this->assertEquals(168.00, (float) $stop->cash_collected);

        $order->refresh();
        $this->assertEquals('delivered', $order->delivery_status);
        $this->assertEquals('paid', $order->payment_status);

        // Verify Payment record was generated
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => 168.00,
            'payment_method' => 'cash',
            'collected_by_user_id' => $staffUser->id,
            'status' => 'successful',
        ]);
    }

    public function test_staff_driver_fails_verification_with_invalid_otp(): void
    {
        $staffUser = User::where('mobile', '9876543212')->first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $staff = $staffUser->staffProfile;
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();

        $order = Order::create([
            'order_number' => 'ORD-RUN-003',
            'idempotency_key' => 'IDEM-RUN-003',
            'customer_profile_id' => $customer->id,
            'branch_id' => $staff->branch_id,
            'delivery_address_id' => $address->id,
            'snapshot_address' => ['area' => 'Anna Nagar'],
            'order_status' => 'confirmed',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'assigned',
            'subtotal_amount' => 80.00,
            'tax_amount' => 0.00,
            'total_amount' => 80.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => Carbon::today()->format('Y-m-d'),
            'delivery_slot_id' => $slot->id,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TODAY-0003',
            'branch_id' => $staff->branch_id,
            'staff_profile_id' => $staff->id,
            'delivery_slot_id' => $slot->id,
            'trip_date' => Carbon::today()->format('Y-m-d'),
            'driver_type' => 'staff',
            'status' => 'in_transit',
        ]);

        $stop = DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'delivery_otp' => '5555',
            'status' => 'pending',
            'cash_to_collect' => 80.00,
            'empty_cans_expected' => 1,
            'empty_cans_collected' => 0,
        ]);

        $response = $this->actingAs($staffUser)->post(route('staff.stops.verify', $stop), [
            'otp' => '0000', // Invalid OTP
            'empty_cans_collected' => 1,
            'cash_collected' => 80.00,
        ]);

        $response->assertSessionHasErrors('otp');

        $stop->refresh();
        $this->assertEquals('pending', $stop->status);
    }

    public function test_staff_driver_can_report_failed_delivery_stop(): void
    {
        $staffUser = User::where('mobile', '9876543212')->first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $staff = $staffUser->staffProfile;
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();

        $order = Order::create([
            'order_number' => 'ORD-RUN-004',
            'idempotency_key' => 'IDEM-RUN-004',
            'customer_profile_id' => $customer->id,
            'branch_id' => $staff->branch_id,
            'delivery_address_id' => $address->id,
            'snapshot_address' => ['area' => 'Anna Nagar'],
            'order_status' => 'confirmed',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'assigned',
            'subtotal_amount' => 80.00,
            'tax_amount' => 0.00,
            'total_amount' => 80.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => Carbon::today()->format('Y-m-d'),
            'delivery_slot_id' => $slot->id,
        ]);

        $trip = DeliveryTrip::create([
            'trip_number' => 'TRIP-TODAY-0004',
            'branch_id' => $staff->branch_id,
            'staff_profile_id' => $staff->id,
            'delivery_slot_id' => $slot->id,
            'trip_date' => Carbon::today()->format('Y-m-d'),
            'driver_type' => 'staff',
            'status' => 'in_transit',
        ]);

        $stop = DeliveryTripStop::create([
            'delivery_trip_id' => $trip->id,
            'order_id' => $order->id,
            'sequence_order' => 1,
            'delivery_otp' => '6789',
            'status' => 'pending',
            'cash_to_collect' => 80.00,
            'empty_cans_expected' => 1,
            'empty_cans_collected' => 0,
        ]);

        $response = $this->actingAs($staffUser)->post(route('staff.stops.fail', $stop), [
            'failure_reason' => 'Door locked, customer unreachable over phone call.',
            'reschedule' => false,
        ]);

        $response->assertSessionHas('success');

        $stop->refresh();
        $this->assertEquals('failed', $stop->status);
        $this->assertEquals('Door locked, customer unreachable over phone call.', $stop->failure_reason);

        $order->refresh();
        $this->assertEquals('failed', $order->delivery_status);
    }

    public function test_staff_driver_can_view_cash_collection_log(): void
    {
        $staffUser = User::where('mobile', '9876543212')->first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();

        $order = Order::create([
            'order_number' => 'ORD-RUN-005',
            'idempotency_key' => 'IDEM-RUN-005',
            'customer_profile_id' => $customer->id,
            'branch_id' => 1,
            'delivery_address_id' => $address->id,
            'snapshot_address' => ['area' => 'Anna Nagar'],
            'order_status' => 'delivered',
            'fulfillment_status' => 'fulfilled',
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
            'subtotal_amount' => 160.00,
            'tax_amount' => 0.00,
            'total_amount' => 160.00,
            'paid_amount' => 160.00,
            'payment_method_preferred' => 'cod',
            'delivery_date' => Carbon::today()->format('Y-m-d'),
            'delivery_slot_id' => $slot->id,
        ]);

        Payment::create([
            'payment_number' => 'PAY-CASH-001',
            'order_id' => $order->id,
            'customer_profile_id' => $customer->id,
            'branch_id' => 1,
            'amount' => 160.00,
            'payment_method' => 'cash',
            'payment_type' => 'order_payment',
            'payment_date' => Carbon::today(),
            'status' => 'successful',
            'collected_by_user_id' => $staffUser->id,
            'notes' => 'Collected in cash at doorstep',
        ]);

        $response = $this->actingAs($staffUser)->get(route('staff.cash-log'));

        $response->assertStatus(200);
        $response->assertSee('PAY-CASH-001');
        $response->assertSee('160.00');
    }

    public function test_customer_can_create_daily_recurring_subscription(): void
    {
        $customerUser = User::where('mobile', '9876543213')->first();
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();
        $product = Product::first();

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $response = $this->actingAs($customerUser)->post(route('customer.subscriptions.store'), [
            'delivery_address_id' => $address->id,
            'delivery_slot_id' => $slot->id,
            'frequency' => 'daily',
            'start_date' => $tomorrow,
            'payment_method' => 'cod',
            'customer_notes' => 'Please leave outside the door',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $subscription = Subscription::latest('id')->first();
        $this->assertNotNull($subscription);
        $response->assertRedirect(route('customer.subscriptions.show', $subscription));

        $this->assertEquals('active', $subscription->status);
        $this->assertEquals('daily', $subscription->frequency);
        $this->assertEquals($customer->id, $subscription->customer_profile_id);
        $this->assertEquals($tomorrow, $subscription->next_delivery_date->format('Y-m-d'));

        // Verify items
        $this->assertDatabaseHas('subscription_items', [
            'subscription_id' => $subscription->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_can_pause_resume_and_cancel_subscription(): void
    {
        $customerUser = User::where('mobile', '9876543213')->first();
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();

        $subscription = Subscription::create([
            'subscription_number' => 'SUB-TEST-0001',
            'customer_profile_id' => $customer->id,
            'branch_id' => $customer->branch_id,
            'delivery_address_id' => $address->id,
            'delivery_slot_id' => $slot->id,
            'frequency' => 'alternate_days',
            'status' => 'active',
            'start_date' => Carbon::today()->format('Y-m-d'),
            'next_delivery_date' => Carbon::tomorrow()->format('Y-m-d'),
            'payment_method' => 'cod',
        ]);

        // Pause
        $response = $this->actingAs($customerUser)->post(route('customer.subscriptions.pause', $subscription));
        $response->assertSessionHas('success');
        $subscription->refresh();
        $this->assertEquals('paused', $subscription->status);

        // Resume
        $response = $this->actingAs($customerUser)->post(route('customer.subscriptions.resume', $subscription));
        $response->assertSessionHas('success');
        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);

        // Cancel
        $response = $this->actingAs($customerUser)->post(route('customer.subscriptions.cancel', $subscription));
        $response->assertSessionHas('success');
        $subscription->refresh();
        $this->assertEquals('cancelled', $subscription->status);
    }

    public function test_artisan_subscriptions_generate_command_auto_creates_orders(): void
    {
        $customerUser = User::where('mobile', '9876543213')->first();
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();
        $product = Product::first();

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $subscription = Subscription::create([
            'subscription_number' => 'SUB-GEN-0001',
            'customer_profile_id' => $customer->id,
            'branch_id' => $customer->branch_id,
            'delivery_address_id' => $address->id,
            'delivery_slot_id' => $slot->id,
            'frequency' => 'daily',
            'status' => 'active',
            'start_date' => $tomorrow,
            'next_delivery_date' => $tomorrow,
            'payment_method' => 'cod',
        ]);

        SubscriptionItem::create([
            'subscription_id' => $subscription->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => $product->selling_price,
        ]);

        // Run Artisan command targeting tomorrow
        $this->artisan('subscriptions:generate', ['--date' => $tomorrow])
            ->assertExitCode(0);

        // Check order was created
        $order = Order::where('subscription_id', $subscription->id)->first();
        $this->assertNotNull($order);
        $this->assertTrue((bool) $order->is_subscription_order);
        $this->assertEquals($tomorrow, $order->delivery_date->format('Y-m-d'));
        $this->assertEquals('confirmed', $order->order_status);

        // Verify order items created
        $item = OrderItem::where('order_id', $order->id)->first();
        $this->assertNotNull($item);
        $this->assertEquals(3, $item->quantity);

        // Verify subscription next_delivery_date advanced by 1 day (daily cadence)
        $subscription->refresh();
        $expectedNext = Carbon::parse($tomorrow)->addDay()->format('Y-m-d');
        $this->assertEquals($expectedNext, $subscription->next_delivery_date->format('Y-m-d'));
        $this->assertEquals($tomorrow, $subscription->last_generated_date->format('Y-m-d'));
    }

    public function test_admin_can_view_subscriptions_and_trigger_manual_generation(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $customerUser = User::where('mobile', '9876543213')->first();
        $customer = $customerUser->customerProfile;
        $address = CustomerAddress::where('customer_profile_id', $customer->id)->first();
        $slot = DeliverySlot::first();

        $sub = Subscription::create([
            'subscription_number' => 'SUB-ADM-0001',
            'customer_profile_id' => $customer->id,
            'branch_id' => 1,
            'delivery_address_id' => $address->id,
            'delivery_slot_id' => $slot->id,
            'frequency' => 'weekly',
            'status' => 'active',
            'start_date' => Carbon::today()->format('Y-m-d'),
            'next_delivery_date' => Carbon::tomorrow()->format('Y-m-d'),
            'payment_method' => 'cod',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.subscriptions.index'));

        $response->assertStatus(200);
        $response->assertSee('SUB-ADM-0001');
        $response->assertSee('Customer Recurring Subscriptions');

        // Test Admin details page
        $detailResponse = $this->actingAs($admin)->get(route('admin.subscriptions.show', $sub));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('SUB-ADM-0001');

        // Test manual generation trigger
        $genResponse = $this->actingAs($admin)->post(route('admin.subscriptions.generate-manual'), [
            'target_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $genResponse->assertSessionHas('success');
    }
}
