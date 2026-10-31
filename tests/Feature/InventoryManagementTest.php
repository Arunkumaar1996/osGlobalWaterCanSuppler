<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Warehouse $warehouse;
    protected Warehouse $warehouse2;
    protected Product $product;
    protected WarehouseStock $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('mobile', '9876543210')->first();
        $this->customer = User::where('mobile', '9876543213')->first();
        $this->warehouse = Warehouse::first();
        $this->warehouse2 = Warehouse::where('id', '!=', $this->warehouse->id)->first();
        $this->product = Product::first();

        // Baseline stock
        $this->stock = WarehouseStock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
    }

    public function test_admin_can_view_warehouse_inventory_overview_and_stock_levels(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.inventory.index'));

        $response->assertStatus(200);
        $response->assertSee('Warehouse Inventory &amp; Container Cycles', false);
        $response->assertSee($this->product->name);
        $response->assertSee('Full Stock Ready');
    }

    public function test_customer_cannot_access_inventory_routes(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.inventory.index'));

        $response->assertStatus(403);
    }

    public function test_reconcile_stock_audit_adjusts_counts_and_logs_audit_ledger_movement(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.inventory.adjust', $this->stock), [
            'full_stock' => 180,
            'empty_stock' => 50,
            'under_cleaning' => 20,
            'damaged_stock' => 3,
            'reorder_level' => 30,
            'reason' => 'Annual physical warehouse inventory count',
        ]);

        $response->assertRedirect();

        $this->stock->refresh();
        $this->assertEquals(180, $this->stock->full_stock);
        $this->assertEquals(50, $this->stock->empty_stock);
        $this->assertEquals(20, $this->stock->under_cleaning);
        $this->assertEquals(3, $this->stock->damaged_stock);
        $this->assertEquals(30, $this->stock->reorder_level);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'stock_audit_adjustment',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_cycle_empty_cans_to_cleaning_bay_and_refilled_full_stock(): void
    {
        // 1. Move 10 cans from empty to cleaning
        $initialEmpty = $this->stock->empty_stock;
        $initialCleaning = $this->stock->under_cleaning;
        $initialFull = $this->stock->full_stock;

        $response = $this->actingAs($this->admin)->post(route('admin.inventory.cycle-cleaning', $this->stock), [
            'action' => 'to_cleaning',
            'quantity' => 10,
            'notes' => 'Batch 501 routed to wash',
        ]);

        $response->assertRedirect();

        $this->stock->refresh();
        $this->assertEquals($initialEmpty - 10, $this->stock->empty_stock);
        $this->assertEquals($initialCleaning + 10, $this->stock->under_cleaning);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'sent_to_cleaning',
            'state_from' => 'empty',
            'state_to' => 'under_cleaning',
            'quantity' => 10,
        ]);

        // 2. Move 10 cans from cleaning to full
        $response2 = $this->actingAs($this->admin)->post(route('admin.inventory.cycle-cleaning', $this->stock), [
            'action' => 'to_full',
            'quantity' => 10,
            'notes' => 'Batch 501 sanitized, refilled, capped and QC passed',
        ]);

        $response2->assertRedirect();

        $this->stock->refresh();
        $this->assertEquals($initialCleaning, $this->stock->under_cleaning);
        $this->assertEquals($initialFull + 10, $this->stock->full_stock);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'refilled_to_full',
            'state_from' => 'under_cleaning',
            'state_to' => 'full',
            'quantity' => 10,
        ]);
    }

    public function test_admin_can_report_and_quarantine_damaged_cans(): void
    {
        $initialFull = $this->stock->full_stock;
        $initialDamaged = $this->stock->damaged_stock;

        $response = $this->actingAs($this->admin)->post(route('admin.inventory.record-damaged', $this->stock), [
            'source_state' => 'full',
            'quantity' => 2,
            'reason' => 'Leaking seam during pressure inspection',
        ]);

        $response->assertRedirect();

        $this->stock->refresh();
        $this->assertEquals($initialFull - 2, $this->stock->full_stock);
        $this->assertEquals($initialDamaged + 2, $this->stock->damaged_stock);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'damaged_writeoff',
            'state_from' => 'full',
            'state_to' => 'damaged',
            'quantity' => 2,
        ]);
    }

    public function test_admin_can_view_physical_inventory_movement_audit_ledger(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.inventory.movements'));

        $response->assertStatus(200);
        $response->assertSee('Inventory Movement Audit Ledger');
        $response->assertSee($this->product->name);
    }

    public function test_inter_warehouse_transfer_dispatches_from_origin_and_completes_at_destination(): void
    {
        $this->assertNotNull($this->warehouse2, 'Secondary warehouse must exist');

        $initialOriginFull = $this->stock->full_stock;

        // Ensure dest warehouse stock record exists
        $destStock = app(InventoryService::class)->getOrCreateStock($this->warehouse2->id, $this->product->id);
        $initialDestFull = $destStock->full_stock;

        // 1. Dispatch Transfer of 25 full cans from Warehouse 1 -> Warehouse 2
        $response = $this->actingAs($this->admin)->post(route('admin.inventory.transfers.store'), [
            'source_warehouse_id' => $this->warehouse->id,
            'destination_warehouse_id' => $this->warehouse2->id,
            'notes' => 'Transfer for southern sector rush',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'stock_type' => 'full',
                    'quantity' => 25,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.inventory.transfers.index'));

        // Verify origin stock decreased by 25
        $this->stock->refresh();
        $this->assertEquals($initialOriginFull - 25, $this->stock->full_stock);

        // Verify transfer record created
        $transfer = StockTransfer::latest('id')->first();
        $this->assertEquals('in_transit', $transfer->status);
        $this->assertDatabaseHas('stock_transfer_items', [
            'stock_transfer_id' => $transfer->id,
            'product_id' => $this->product->id,
            'quantity_sent' => 25,
        ]);

        $item = $transfer->items->first();

        // 2. Complete Transfer at destination: 24 accepted, 1 damaged in transit
        $response2 = $this->actingAs($this->admin)->post(route('admin.inventory.transfers.complete', $transfer), [
            'notes' => 'Truck arrived; 1 can dropped on unloading',
            'items' => [
                [
                    'stock_transfer_item_id' => $item->id,
                    'quantity_received' => 24,
                    'quantity_damaged_in_transit' => 1,
                ],
            ],
        ]);

        $response2->assertRedirect(route('admin.inventory.transfers.index'));

        // Verify transfer status completed
        $transfer->refresh();
        $this->assertEquals('completed', $transfer->status);

        // Verify destination stock received 24 full, 1 damaged
        $destStock->refresh();
        $this->assertEquals($initialDestFull + 24, $destStock->full_stock);
        $this->assertEquals(1, $destStock->damaged_stock);
    }

    public function test_recording_grn_automatically_updates_warehouse_full_stock_and_movement_ledger(): void
    {
        $supplier = Supplier::first();
        $initialFullStock = $this->stock->full_stock;

        // 1. Create a PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-WH-01',
            'supplier_id' => $supplier->id,
            'branch_id' => $this->warehouse->branch_id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'issued',
            'total_amount' => 2000.00,
            'expected_delivery_date' => now()->addDays(1),
            'created_by_user_id' => $this->admin->id,
        ]);

        $poItem = $po->items()->create([
            'product_id' => $this->product->id,
            'ordered_quantity' => 50,
            'received_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_price' => 40.00,
            'subtotal' => 2000.00,
        ]);

        // 2. Record GRN accepting 45 cans (5 rejected)
        $this->actingAs($this->admin)->post(route('admin.procurement.grn.store', $po), [
            'supplier_challan_no' => 'DC-WH-99',
            'remarks' => '45 cans received into warehouse inventory',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_accepted' => 45,
                    'quantity_damaged' => 5,
                ],
            ],
        ]);

        // 3. Verify warehouse stock full_stock increased by 45
        $this->stock->refresh();
        $this->assertEquals($initialFullStock + 45, $this->stock->full_stock);

        // 4. Verify inventory movement ledger entry
        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'purchase_received',
            'state_from' => 'none',
            'state_to' => 'full',
            'quantity' => 45,
        ]);
    }
}
