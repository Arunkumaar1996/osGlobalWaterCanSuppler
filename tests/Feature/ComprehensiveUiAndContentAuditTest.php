<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveUiAndContentAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $customer;
    protected Branch $branch;
    protected Product $product;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('mobile', '9876543210')->first();
        $this->staff = User::where('mobile', '9876543212')->first();
        $this->customer = User::where('mobile', '9876543213')->first();
        $this->branch = Branch::first();
        $this->product = Product::first();
        $this->supplier = Supplier::first();
    }

    public function test_all_public_guest_pages_render_without_errors(): void
    {
        // 1. Landing Page
        $home = $this->get(route('home'));
        $home->assertStatus(200);
        $home->assertSee('AquaFlow');
        $home->assertSee('Our Water Can Capacities');
        $home->assertSee('Cart');

        // 2. Cart Page (Empty State)
        $cart = $this->get(route('cart.index'));
        $cart->assertStatus(200);
        $cart->assertSee('Your Water Cart');
        $cart->assertSee('Your Cart is Currently Empty');

        // 3. Login Page
        $login = $this->get(route('login'));
        $login->assertStatus(200);
        $login->assertSee('Sign In to Your Account');
        $login->assertSee('Mobile Number or Email');

        // 4. Register Page
        $register = $this->get(route('register'));
        $register->assertStatus(200);
        $register->assertSee('Create Your Customer Account');
        $register->assertSee('Default Delivery Address');

        // 5. Offline Fallback View
        $offline = $this->get(route('offline'));
        $offline->assertStatus(200);
        $offline->assertSee("You're Currently Offline", false);
    }

    public function test_all_customer_portal_pages_render_without_errors(): void
    {
        // 1. Customer Dashboard
        $dashboard = $this->actingAs($this->customer)->get(route('customer.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Welcome, ' . $this->customer->name, false);
        $dashboard->assertSee('Outstanding Dues');
        $dashboard->assertSee('Containers Held');

        // 2. Customer Order History
        $orders = $this->actingAs($this->customer)->get(route('customer.orders.index'));
        $orders->assertStatus(200);
        $orders->assertSee('Order History');

        // 3. Customer Dues & Online Settlement
        $dues = $this->actingAs($this->customer)->get(route('customer.dues.index'));
        $dues->assertStatus(200);
        $dues->assertSee('Outstanding Balance & Dues', false);

        // 4. Customer Financial Ledger Passbook
        $ledger = $this->actingAs($this->customer)->get(route('customer.ledger.index'));
        $ledger->assertStatus(200);
        $ledger->assertSee('Financial Passbook');

        // 5. Customer Can Exchange Balance
        $cans = $this->actingAs($this->customer)->get(route('customer.cans.index'));
        $cans->assertStatus(200);
        $cans->assertSee('Empty-Can Exchange Balance');
        $cans->assertSee('Container Movement Passbook');

        // 6. Customer Subscriptions Index & Create
        $subs = $this->actingAs($this->customer)->get(route('customer.subscriptions.index'));
        $subs->assertStatus(200);
        $subs->assertSee('Recurring Water Subscriptions');

        $subsCreate = $this->actingAs($this->customer)->get(route('customer.subscriptions.create'));
        $subsCreate->assertStatus(200);
        $subsCreate->assertSee('Set Up Recurring Water Subscription');

        // 7. Checkout Page with active item
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
        $checkout = $this->actingAs($this->customer)->get(route('checkout.index'));
        $checkout->assertStatus(200);
        $checkout->assertSee('Order Checkout');
        $checkout->assertSee('Delivery Address');
        $checkout->assertSee('Delivery Date & Slot', false);
        $checkout->assertSee('Payment Method');
    }

    public function test_all_staff_portal_pages_render_without_errors(): void
    {
        // 1. Staff Delivery Runsheet Portal
        $deliveries = $this->actingAs($this->staff)->get(route('staff.deliveries'));
        $deliveries->assertStatus(200);
        $deliveries->assertSee('Assigned Deliveries');

        // 2. Staff Cash Log
        $cashLog = $this->actingAs($this->staff)->get(route('staff.cash-log'));
        $cashLog->assertStatus(200);
        $cashLog->assertSee('Collected Cash Receipts');
        $cashLog->assertSee('Total Cash in Hand');
    }

    public function test_all_admin_console_pages_render_without_errors(): void
    {
        // 1. Admin Dashboard
        $dashboard = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('AquaFlow');

        // 2. Products Catalog
        $products = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $products->assertStatus(200);
        $products->assertSee('Product Catalog');

        $prodCreate = $this->actingAs($this->admin)->get(route('admin.products.create'));
        $prodCreate->assertStatus(200);

        $prodEdit = $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product));
        $prodEdit->assertStatus(200);

        $prodHistory = $this->actingAs($this->admin)->get(route('admin.products.price-history', $this->product));
        $prodHistory->assertStatus(200);

        // 3. Suppliers & Procurement
        $suppliers = $this->actingAs($this->admin)->get(route('admin.suppliers.index'));
        $suppliers->assertStatus(200);

        $suppCreate = $this->actingAs($this->admin)->get(route('admin.suppliers.create'));
        $suppCreate->assertStatus(200);

        $suppShow = $this->actingAs($this->admin)->get(route('admin.suppliers.show', $this->supplier));
        $suppShow->assertStatus(200);

        $procurement = $this->actingAs($this->admin)->get(route('admin.procurement.index'));
        $procurement->assertStatus(200);

        $procCreate = $this->actingAs($this->admin)->get(route('admin.procurement.create'));
        $procCreate->assertStatus(200);

        // 4. Inventory & Warehouse
        $inventory = $this->actingAs($this->admin)->get(route('admin.inventory.index'));
        $inventory->assertStatus(200);

        $invMovements = $this->actingAs($this->admin)->get(route('admin.inventory.movements'));
        $invMovements->assertStatus(200);

        $transfers = $this->actingAs($this->admin)->get(route('admin.inventory.transfers.index'));
        $transfers->assertStatus(200);

        $transfersCreate = $this->actingAs($this->admin)->get(route('admin.inventory.transfers.create'));
        $transfersCreate->assertStatus(200);

        // 5. Customer Orders & Manifests
        $orders = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $orders->assertStatus(200);

        $manifests = $this->actingAs($this->admin)->get(route('admin.manifests.index'));
        $manifests->assertStatus(200);

        $manifestsCreate = $this->actingAs($this->admin)->get(route('admin.manifests.create'));
        $manifestsCreate->assertStatus(200);

        // 6. Financial Accounts, Dues & Payments
        $accounts = $this->actingAs($this->admin)->get(route('admin.accounts.index'));
        $accounts->assertStatus(200);

        $customerLedger = $this->actingAs($this->admin)->get(route('admin.accounts.customer', $this->customer->customerProfile));
        $customerLedger->assertStatus(200);

        $payments = $this->actingAs($this->admin)->get(route('admin.payments.index'));
        $payments->assertStatus(200);

        // 7. Empty-Can Circulation & Fleet
        $cans = $this->actingAs($this->admin)->get(route('admin.cans.index'));
        $cans->assertStatus(200);

        $customerCans = $this->actingAs($this->admin)->get(route('admin.cans.customer', $this->customer->customerProfile));
        $customerCans->assertStatus(200);

        $canMovements = $this->actingAs($this->admin)->get(route('admin.cans.movements'));
        $canMovements->assertStatus(200);

        // 8. Delivery Dispatch, Partners & Capacity Slots
        $delivery = $this->actingAs($this->admin)->get(route('admin.delivery.index'));
        $delivery->assertStatus(200);

        $tripCreate = $this->actingAs($this->admin)->get(route('admin.delivery.trips.create'));
        $tripCreate->assertStatus(200);

        $partners = $this->actingAs($this->admin)->get(route('admin.delivery.partners.index'));
        $partners->assertStatus(200);

        $slots = $this->actingAs($this->admin)->get(route('admin.delivery.slots.index'));
        $slots->assertStatus(200);

        // 9. Subscriptions & Expenses
        $subscriptions = $this->actingAs($this->admin)->get(route('admin.subscriptions.index'));
        $subscriptions->assertStatus(200);

        $expenses = $this->actingAs($this->admin)->get(route('admin.expenses.index'));
        $expenses->assertStatus(200);

        $expensesCreate = $this->actingAs($this->admin)->get(route('admin.expenses.create'));
        $expensesCreate->assertStatus(200);

        // 10. Reports & Analytics
        $reports = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $reports->assertStatus(200);

        $pnl = $this->actingAs($this->admin)->get(route('admin.reports.pnl'));
        $pnl->assertStatus(200);
        $pnl->assertSee('Profit & Loss (P&L) Statement', false);

        $containersReport = $this->actingAs($this->admin)->get(route('admin.reports.containers'));
        $containersReport->assertStatus(200);

        $fleetReport = $this->actingAs($this->admin)->get(route('admin.reports.fleet'));
        $fleetReport->assertStatus(200);

        // 11. Users, Access Control & Settings
        $users = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $users->assertStatus(200);

        $userCreate = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $userCreate->assertStatus(200);

        $permissions = $this->actingAs($this->admin)->get(route('admin.users.permissions'));
        $permissions->assertStatus(200);
        $permissions->assertSee('Role-Based Access Control (RBAC) Matrix');

        $userShow = $this->actingAs($this->admin)->get(route('admin.users.show', $this->customer));
        $userShow->assertStatus(200);

        $userEdit = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->customer));
        $userEdit->assertStatus(200);

        $settings = $this->actingAs($this->admin)->get(route('admin.settings.index'));
        $settings->assertStatus(200);
        $settings->assertSee('System & Branch Settings', false);
    }
}
