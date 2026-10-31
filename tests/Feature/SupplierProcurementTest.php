<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierLedger;
use App\Models\SupplierProduct;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierProcurementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Supplier $supplier;
    protected Warehouse $warehouse;
    protected Branch $branch;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('mobile', '9876543210')->first();
        $this->customer = User::where('mobile', '9876543213')->first();
        $this->supplier = Supplier::first();
        $this->warehouse = Warehouse::first();
        $this->branch = Branch::first();
        $this->product = Product::first();
    }

    public function test_admin_can_view_suppliers_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('Water Suppliers & Manufacturers');
        $response->assertSee($this->supplier->company_name);
    }

    public function test_customer_cannot_access_supplier_routes(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.suppliers.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_new_supplier(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.suppliers.store'), [
            'company_name' => 'Crystal Blue Bottlers Pvt Ltd',
            'contact_person' => 'Vikram Seth',
            'phone' => '9845012399',
            'email' => 'sales@crystalblue.com',
            'gst_number' => '29AABCC1234F1Z0',
            'pan_number' => 'AABCC1234F',
            'address' => 'Plot 45, Peenya Industrial Area',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'pincode' => '560058',
            'bank_name' => 'ICICI Bank',
            'bank_account_no' => '000201009988',
            'bank_ifsc_code' => 'ICIC0000002',
            'notes' => 'Delivers 20L cans within 12 hours',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('suppliers', [
            'company_name' => 'Crystal Blue Bottlers Pvt Ltd',
            'phone' => '9845012399',
            'outstanding_payable_balance' => 0.00,
        ]);

        $supplier = Supplier::where('phone', '9845012399')->first();
        $response->assertRedirect(route('admin.suppliers.show', $supplier));
    }

    public function test_admin_can_map_product_to_supplier(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.suppliers.products.store', $this->supplier), [
            'product_id' => $this->product->id,
            'supplier_sku' => 'SUPP-CAN-20L',
            'purchase_price' => 38.50,
            'lead_time_hours' => 18,
            'is_preferred' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'purchase_price' => 38.50,
            'lead_time_hours' => 18,
            'is_preferred' => 1,
        ]);
    }

    public function test_admin_can_issue_purchase_order(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.procurement.store'), [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'expected_delivery_date' => date('Y-m-d', strtotime('+2 days')),
            'notes' => 'Early morning delivery preferred',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 100,
                    'unit_price' => 40.00,
                ],
            ],
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'status' => 'issued',
            'total_amount' => 4000.00,
        ]);

        $po = PurchaseOrder::where('supplier_id', $this->supplier->id)->latest('id')->first();
        $response->assertRedirect(route('admin.procurement.show', $po));

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'ordered_quantity' => 100,
            'received_quantity' => 0,
            'unit_price' => 40.00,
            'subtotal' => 4000.00,
        ]);
    }

    public function test_goods_receipt_note_updates_stock_inspection_and_posts_supplier_liability(): void
    {
        // 1. Create a PO with 100 items @ 40.00 = 4,000.00
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'issued',
            'total_amount' => 4000.00,
            'expected_delivery_date' => now()->addDays(2),
            'created_by_user_id' => $this->admin->id,
        ]);

        $poItem = $po->items()->create([
            'product_id' => $this->product->id,
            'ordered_quantity' => 100,
            'received_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_price' => 40.00,
            'subtotal' => 4000.00,
        ]);

        $initialSupplierBalance = (float) $this->supplier->outstanding_payable_balance;

        // 2. Post GRN: 80 cans accepted, 5 cans damaged/rejected (15 still pending)
        $response = $this->actingAs($this->admin)->post(route('admin.procurement.grn.store', $po), [
            'supplier_challan_no' => 'CH-8899',
            'remarks' => 'Truck arrived at 8:00 AM; 5 leaking caps rejected',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_accepted' => 80,
                    'quantity_damaged' => 5,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.procurement.show', $po));

        // 3. Verify PO item updated
        $poItem->refresh();
        $this->assertEquals(80, $poItem->received_quantity);
        $this->assertEquals(5, $poItem->rejected_quantity);

        // 4. Verify PO status is 'partially_received'
        $po->refresh();
        $this->assertEquals('partially_received', $po->status);

        // 5. Verify Goods Receipt record
        $this->assertDatabaseHas('goods_receipts', [
            'purchase_order_id' => $po->id,
            'supplier_challan_no' => 'CH-8899',
        ]);

        // 6. Verify Supplier Invoice created for accepted quantity (80 * 40 = 3200)
        $expectedBilledAmount = 80 * 40.00;
        $this->assertDatabaseHas('supplier_invoices', [
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'total_amount' => $expectedBilledAmount,
            'status' => 'unpaid',
        ]);

        // 7. Verify Supplier Ledger entry credited
        $this->assertDatabaseHas('supplier_ledger', [
            'supplier_id' => $this->supplier->id,
            'transaction_type' => 'invoice_billed',
            'credit_amount' => $expectedBilledAmount,
            'debit_amount' => 0.00,
        ]);

        // 8. Verify Supplier Outstanding Payable balance updated
        $this->supplier->refresh();
        $this->assertEquals($initialSupplierBalance + $expectedBilledAmount, (float) $this->supplier->outstanding_payable_balance);
    }

    public function test_recording_supplier_payment_debits_ledger_and_reduces_payable_balance(): void
    {
        // Set an outstanding balance on supplier
        $this->supplier->update(['outstanding_payable_balance' => 5000.00]);

        $response = $this->actingAs($this->admin)->post(route('admin.suppliers.payments.store', $this->supplier), [
            'amount' => 2000.00,
            'payment_mode' => 'bank_transfer',
            'transaction_reference' => 'UTR123456789',
            'payment_date' => date('Y-m-d'),
            'notes' => 'Weekly payment settlement',
        ]);

        $response->assertRedirect();

        // Check payment record
        $this->assertDatabaseHas('supplier_payments', [
            'supplier_id' => $this->supplier->id,
            'amount' => 2000.00,
            'payment_mode' => 'bank_transfer',
            'transaction_reference' => 'UTR123456789',
        ]);

        // Check ledger debit
        $this->assertDatabaseHas('supplier_ledger', [
            'supplier_id' => $this->supplier->id,
            'transaction_type' => 'payment_made',
            'debit_amount' => 2000.00,
            'credit_amount' => 0.00,
            'running_balance' => 3000.00,
        ]);

        // Check supplier balance
        $this->supplier->refresh();
        $this->assertEquals(3000.00, (float) $this->supplier->outstanding_payable_balance);
    }
}
