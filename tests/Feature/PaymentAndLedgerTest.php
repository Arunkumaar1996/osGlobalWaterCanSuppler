<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\CustomerLedger;
use App\Models\CustomerProfile;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_corporate_customer_placing_pay_later_order_debits_ledger_and_updates_balance(): void
    {
        $corporate = User::where('mobile', '9876543214')->first();
        $profile = $corporate->customerProfile;
        $initialBalance = (float) $profile->outstanding_financial_balance;

        $product = Product::where('slug', '20-litre-bubble-top-water-can')->first();
        $address = CustomerAddress::where('customer_profile_id', $profile->id)->first();
        $slot = DeliverySlot::first();

        $this->actingAs($corporate)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $deliveryDate = Carbon::today()->addDay()->format('Y-m-d');

        $response = $this->actingAs($corporate)->post('/checkout/process', [
            'delivery_address_id' => $address->id,
            'delivery_date' => $deliveryDate,
            'delivery_slot_id' => $slot->id,
            'payment_method' => 'pay_later',
        ]);

        $order = Order::where('customer_profile_id', $profile->id)->latest('id')->first();
        $this->assertNotNull($order);
        $expectedTotal = (float) $order->total_amount;

        // Check customer financial balance was increased by order total
        $profile->refresh();
        $this->assertEquals($initialBalance + $expectedTotal, (float) $profile->outstanding_financial_balance);

        // Check double-entry customer_ledger entry was posted
        $this->assertDatabaseHas('customer_ledger', [
            'customer_profile_id' => $profile->id,
            'transaction_type' => 'order_charge',
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'debit_amount' => $expectedTotal,
            'credit_amount' => 0.00,
            'running_balance' => $initialBalance + $expectedTotal,
        ]);
    }

    public function test_customer_exceeding_credit_limit_cannot_place_pay_later_order(): void
    {
        $corporate = User::where('mobile', '9876543214')->first();
        $profile = $corporate->customerProfile;

        // Set high initial due close to limit
        $profile->update([
            'credit_limit' => 500.00,
            'outstanding_financial_balance' => 450.00,
        ]);

        $product = Product::where('slug', '20-litre-bubble-top-water-can')->first();
        $address = CustomerAddress::where('customer_profile_id', $profile->id)->first();
        $slot = DeliverySlot::first();

        // 5 cans * ₹80 = ₹400 (+ ₹450 due = ₹850 > ₹500 limit)
        $this->actingAs($corporate)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $deliveryDate = Carbon::today()->addDay()->format('Y-m-d');

        $response = $this->actingAs($corporate)->post('/checkout/process', [
            'delivery_address_id' => $address->id,
            'delivery_date' => $deliveryDate,
            'delivery_slot_id' => $slot->id,
            'payment_method' => 'pay_later',
        ]);

        $response->assertSessionHasErrors('checkout');
        $this->assertEquals(450.00, (float) $profile->fresh()->outstanding_financial_balance);
    }

    public function test_customer_can_view_dues_page_and_ledger_passbook(): void
    {
        $customer = User::where('mobile', '9876543213')->first();

        // 1. Dues page
        $duesResponse = $this->actingAs($customer)->get(route('customer.dues.index'));
        $duesResponse->assertStatus(200);
        $duesResponse->assertSee('Outstanding Balance');
        $duesResponse->assertSee('Instant Online Settlement');

        // 2. Ledger passbook
        $ledgerResponse = $this->actingAs($customer)->get(route('customer.ledger.index'));
        $ledgerResponse->assertStatus(200);
        $ledgerResponse->assertSee('Customer Financial Passbook');
        $ledgerResponse->assertSee('Transaction History');
    }

    public function test_customer_can_create_razorpay_order_checkout(): void
    {
        $corporate = User::where('mobile', '9876543214')->first();
        $profile = $corporate->customerProfile;

        $order = Order::create([
            'order_number' => 'ORD-TEST-0001',
            'idempotency_key' => 'IDEM-TEST-0001',
            'customer_profile_id' => $profile->id,
            'branch_id' => $profile->branch_id,
            'snapshot_address' => ['city' => 'Chennai'],
            'order_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal_amount' => 300.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'total_amount' => 300.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'razorpay',
            'delivery_date' => Carbon::tomorrow(),
            'delivery_slot_id' => DeliverySlot::first()->id,
        ]);

        $response = $this->actingAs($corporate)->postJson(route('customer.orders.checkout', $order->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'currency' => 'INR',
            'amount' => 30000, // 300 * 100 paise
            'remaining_due' => 300.00,
        ]);
        $this->assertNotEmpty($response->json('razorpay_order_id'));
    }

    public function test_customer_can_verify_razorpay_payment_which_credits_ledger_and_marks_order_paid(): void
    {
        $corporate = User::where('mobile', '9876543214')->first();
        $profile = $corporate->customerProfile;
        $profile->update(['outstanding_financial_balance' => 300.00]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-0002',
            'idempotency_key' => 'IDEM-TEST-0002',
            'customer_profile_id' => $profile->id,
            'branch_id' => $profile->branch_id,
            'snapshot_address' => ['city' => 'Chennai'],
            'order_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal_amount' => 300.00,
            'tax_amount' => 0.00,
            'delivery_charge' => 0.00,
            'total_amount' => 300.00,
            'paid_amount' => 0.00,
            'payment_method_preferred' => 'razorpay',
            'delivery_date' => Carbon::tomorrow(),
            'delivery_slot_id' => DeliverySlot::first()->id,
        ]);

        $response = $this->actingAs($corporate)->post(route('customer.orders.verify', $order->id), [
            'razorpay_order_id' => 'order_mock_test12345',
            'razorpay_payment_id' => 'pay_mock_test12345',
            'razorpay_signature' => 'mock_signature_valid',
            'amount' => 300.00,
        ]);

        $response->assertRedirect(route('customer.orders.show', $order->id));

        // Verify Payment row created
        $this->assertDatabaseHas('payments', [
            'customer_profile_id' => $profile->id,
            'order_id' => $order->id,
            'amount' => 300.00,
            'payment_method' => 'razorpay',
            'status' => 'successful',
            'transaction_reference' => 'pay_mock_test12345',
        ]);

        // Verify Order updated
        $order->refresh();
        $this->assertEquals(300.00, (float) $order->paid_amount);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->order_status);

        // Verify Customer balance reduced
        $profile->refresh();
        $this->assertEquals(0.00, (float) $profile->outstanding_financial_balance);

        // Verify Customer Ledger Credit entry
        $this->assertDatabaseHas('customer_ledger', [
            'customer_profile_id' => $profile->id,
            'transaction_type' => 'payment_received',
            'credit_amount' => 300.00,
            'debit_amount' => 0.00,
            'running_balance' => 0.00,
        ]);
    }

    public function test_customer_can_settle_past_dues_online(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $profile = $customer->customerProfile;
        $profile->update(['outstanding_financial_balance' => 500.00]);

        $response = $this->actingAs($customer)->post(route('customer.dues.verify'), [
            'razorpay_order_id' => 'order_mock_settle123',
            'razorpay_payment_id' => 'pay_mock_settle123',
            'razorpay_signature' => 'mock_valid_signature',
            'amount' => 250.00, // Partial settlement
        ]);

        $response->assertRedirect(route('customer.dues.index'));

        // Balance reduced from 500 to 250
        $profile->refresh();
        $this->assertEquals(250.00, (float) $profile->outstanding_financial_balance);

        $this->assertDatabaseHas('customer_ledger', [
            'customer_profile_id' => $profile->id,
            'transaction_type' => 'payment_received',
            'credit_amount' => 250.00,
            'running_balance' => 250.00,
        ]);
    }

    public function test_razorpay_webhook_idempotently_processes_payment_capture(): void
    {
        $customer = User::where('mobile', '9876543213')->first();
        $profile = $customer->customerProfile;
        $profile->update(['outstanding_financial_balance' => 400.00]);

        $webhookPayload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_webhook_unique_999',
                        'amount' => 40000, // paise = ₹400
                        'order_id' => 'order_webhook_999',
                        'notes' => [
                            'customer_id' => $profile->id,
                        ],
                    ],
                ],
            ],
        ];

        // First webhook delivery
        $response1 = $this->postJson(route('razorpay.webhook'), $webhookPayload);
        $response1->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'transaction_reference' => 'pay_webhook_unique_999',
            'amount' => 400.00,
        ]);
        $this->assertEquals(0.00, (float) $profile->fresh()->outstanding_financial_balance);

        // Second identical webhook delivery (idempotency check)
        $response2 = $this->postJson(route('razorpay.webhook'), $webhookPayload);
        $response2->assertStatus(200);

        // Ensure no duplicate payment rows
        $this->assertEquals(1, Payment::where('transaction_reference', 'pay_webhook_unique_999')->count());
        $this->assertEquals(0.00, (float) $profile->fresh()->outstanding_financial_balance);
    }

    public function test_admin_can_view_accounts_overview_and_receivables(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Financial Accounts &amp; Receivables', false);
        $response->assertSee('Total Receivables');
        $response->assertSee('Collections Today');
    }

    public function test_admin_can_record_manual_collection(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $customer = CustomerProfile::first();
        $customer->update(['outstanding_financial_balance' => 600.00]);

        $response = $this->actingAs($admin)->post(route('admin.accounts.collect', $customer->id), [
            'amount' => 600.00,
            'payment_method' => 'cash',
            'transaction_reference' => 'CASH-REC-01',
            'notes' => 'Received cash in office',
        ]);

        $response->assertSessionHas('success');

        // Customer due should now be 0
        $customer->refresh();
        $this->assertEquals(0.00, (float) $customer->outstanding_financial_balance);

        // Check payment record
        $this->assertDatabaseHas('payments', [
            'customer_profile_id' => $customer->id,
            'amount' => 600.00,
            'payment_method' => 'cash',
            'collected_by_user_id' => $admin->id,
        ]);

        // Check ledger entry
        $this->assertDatabaseHas('customer_ledger', [
            'customer_profile_id' => $customer->id,
            'transaction_type' => 'payment_received',
            'credit_amount' => 600.00,
            'running_balance' => 0.00,
        ]);
    }

    public function test_admin_can_record_manual_balance_adjustment(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $customer = CustomerProfile::first();
        $customer->update(['outstanding_financial_balance' => 500.00]);

        // 1. Credit waiver (reduces balance from 500 to 400)
        $resp1 = $this->actingAs($admin)->post(route('admin.accounts.adjust', $customer->id), [
            'amount' => 100.00,
            'adjustment_type' => 'credit',
            'reason' => 'Customer goodwill promotional waiver',
        ]);
        $resp1->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals(400.00, (float) $customer->outstanding_financial_balance);

        $this->assertDatabaseHas('customer_ledger', [
            'customer_profile_id' => $customer->id,
            'transaction_type' => 'credit_adjustment',
            'credit_amount' => 100.00,
            'running_balance' => 400.00,
        ]);

        // 2. Debit penalty (increases balance from 400 to 450)
        $resp2 = $this->actingAs($admin)->post(route('admin.accounts.adjust', $customer->id), [
            'amount' => 50.00,
            'adjustment_type' => 'debit',
            'reason' => 'Lost bottle penalty fee charge',
        ]);
        $resp2->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals(450.00, (float) $customer->outstanding_financial_balance);

        $this->assertDatabaseHas('customer_ledger', [
            'customer_profile_id' => $customer->id,
            'transaction_type' => 'debit_adjustment',
            'debit_amount' => 50.00,
            'running_balance' => 450.00,
        ]);
    }
}
