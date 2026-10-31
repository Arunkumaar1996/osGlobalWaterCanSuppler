<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Display warehouse stock levels and category breakdown
     */
    public function index(Request $request): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $selectedWarehouseId = $request->input('warehouse_id', $warehouses->first()?->id);
        $selectedWarehouse = $warehouses->firstWhere('id', $selectedWarehouseId) ?: $warehouses->first();

        $stocks = collect();
        $summary = [
            'total_full' => 0,
            'total_empty' => 0,
            'total_cleaning' => 0,
            'total_damaged' => 0,
            'low_stock_count' => 0,
        ];

        if ($selectedWarehouse) {
            // Ensure all active products exist in warehouse stock
            $activeProducts = Product::where('is_active', true)->get();
            foreach ($activeProducts as $p) {
                $this->inventoryService->getOrCreateStock($selectedWarehouse->id, $p->id);
            }

            $stocks = WarehouseStock::where('warehouse_id', $selectedWarehouse->id)
                ->with(['product.category', 'canType'])
                ->get()
                ->sortBy('product.name');

            $summary['total_full'] = $stocks->sum('full_stock');
            $summary['total_empty'] = $stocks->sum('empty_stock');
            $summary['total_cleaning'] = $stocks->sum('under_cleaning');
            $summary['total_damaged'] = $stocks->sum('damaged_stock');
            $summary['low_stock_count'] = $stocks->filter(fn($s) => $s->is_low_stock)->count();
        }

        return view('admin.inventory.index', compact('warehouses', 'selectedWarehouse', 'stocks', 'summary'));
    }

    /**
     * Reconcile physical warehouse stock audit counts
     */
    public function adjust(Request $request, WarehouseStock $warehouseStock): RedirectResponse
    {
        $validated = $request->validate([
            'full_stock' => ['required', 'integer', 'min:0'],
            'empty_stock' => ['required', 'integer', 'min:0'],
            'under_cleaning' => ['required', 'integer', 'min:0'],
            'damaged_stock' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->inventoryService->reconcileStockAudit(
            warehouseId: $warehouseStock->warehouse_id,
            productId: $warehouseStock->product_id,
            actualCounts: [
                'full' => (int) $validated['full_stock'],
                'empty' => (int) $validated['empty_stock'],
                'under_cleaning' => (int) $validated['under_cleaning'],
                'damaged' => (int) $validated['damaged_stock'],
                'reorder_level' => (int) $validated['reorder_level'],
            ],
            admin: $request->user(),
            reason: $validated['reason']
        );

        return back()->with('success', "Stock count for '{$warehouseStock->product?->name}' reconciled successfully.");
    }

    /**
     * Move cans through cleaning and refilling lifecycle
     */
    public function cycleCleaning(Request $request, WarehouseStock $warehouseStock): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:to_cleaning,to_full'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = (int) $validated['quantity'];

        if ($validated['action'] === 'to_cleaning') {
            if ($warehouseStock->empty_stock < $qty) {
                return back()->with('error', "Cannot move {$qty} cans. Only {$warehouseStock->empty_stock} empty cans available in warehouse.");
            }

            $this->inventoryService->sendEmptyToCleaning(
                warehouseId: $warehouseStock->warehouse_id,
                productId: $warehouseStock->product_id,
                quantity: $qty,
                user: $request->user(),
                notes: $validated['notes']
            );

            $msg = "{$qty} cans transferred to the washing and sanitization bay.";
        } else {
            if ($warehouseStock->under_cleaning < $qty) {
                return back()->with('error', "Cannot complete refill for {$qty} cans. Only {$warehouseStock->under_cleaning} cans currently in washing bay.");
            }

            $this->inventoryService->completeRefillToFull(
                warehouseId: $warehouseStock->warehouse_id,
                productId: $warehouseStock->product_id,
                quantity: $qty,
                user: $request->user(),
                notes: $validated['notes']
            );

            $msg = "{$qty} sanitized cans refilled, sealed, and added to available full stock.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Quarantine damaged or defective cans
     */
    public function recordDamaged(Request $request, WarehouseStock $warehouseStock): RedirectResponse
    {
        $validated = $request->validate([
            'source_state' => ['required', 'string', 'in:full,empty,under_cleaning'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $sourceState = $validated['source_state'];
        $column = $sourceState . '_stock';
        if ($sourceState === 'under_cleaning') {
            $column = 'under_cleaning';
        }

        $qty = (int) $validated['quantity'];
        if ($warehouseStock->$column < $qty) {
            return back()->with('error', "Cannot quarantine {$qty} cans. Only {$warehouseStock->$column} available in {$sourceState} state.");
        }

        $this->inventoryService->markAsDamaged(
            warehouseId: $warehouseStock->warehouse_id,
            productId: $warehouseStock->product_id,
            sourceState: $sourceState,
            quantity: $qty,
            user: $request->user(),
            reason: $validated['reason']
        );

        return back()->with('success', "{$qty} cans quarantined as damaged ({$validated['reason']}).");
    }

    /**
     * Immutable physical inventory movement ledger
     */
    public function movements(Request $request): View
    {
        $query = InventoryMovement::with(['warehouse', 'product', 'user', 'reference'])
            ->latest('id');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $movements = $query->paginate(25)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.inventory.movements', compact('movements', 'warehouses', 'products'));
    }

    /**
     * Inter-warehouse stock transfers listing
     */
    public function transfersIndex(Request $request): View
    {
        $query = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'initiatedBy', 'receivedBy', 'items.product'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $transfers = $query->paginate(15)->withQueryString();

        return view('admin.inventory.transfers.index', compact('transfers'));
    }

    /**
     * Show form to initiate an inter-warehouse transfer
     */
    public function transfersCreate(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with('canType')->orderBy('name')->get();

        return view('admin.inventory.transfers.create', compact('warehouses', 'products'));
    }

    /**
     * Store and dispatch an inter-warehouse transfer
     */
    public function transfersStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_warehouse_id' => ['required', 'exists:warehouses,id'],
            'destination_warehouse_id' => ['required', 'exists:warehouses,id', 'different:source_warehouse_id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.stock_type' => ['required', 'string', 'in:full,empty,damaged'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $transfer = $this->inventoryService->initiateTransfer(
            sourceWhId: (int) $validated['source_warehouse_id'],
            destWhId: (int) $validated['destination_warehouse_id'],
            items: $validated['items'],
            user: $request->user(),
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('admin.inventory.transfers.index')
            ->with('success', "Transfer {$transfer->transfer_number} dispatched in-transit to {$transfer->destinationWarehouse?->name}.");
    }

    /**
     * Complete an incoming inter-warehouse transfer
     */
    public function transfersComplete(Request $request, StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status === 'completed') {
            return back()->with('error', 'Transfer is already marked as completed.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_transfer_item_id' => ['required', 'exists:stock_transfer_items,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
            'items.*.quantity_damaged_in_transit' => ['required', 'integer', 'min:0'],
        ]);

        $this->inventoryService->completeTransfer(
            transfer: $stockTransfer,
            receivedItems: $validated['items'],
            user: $request->user(),
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('admin.inventory.transfers.index')
            ->with('success', "Transfer {$stockTransfer->transfer_number} received and stocked at destination warehouse.");
    }
}
