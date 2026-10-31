<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementController extends Controller
{
    public function __construct(
        protected SupplierService $supplierService
    ) {}

    /**
     * Display listing of Purchase Orders
     */
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'branch', 'items.product', 'createdBy'])
            ->latest();

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('company_name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('admin.procurement.index', compact('purchaseOrders', 'suppliers', 'warehouses'));
    }

    /**
     * Show form for creating a new Purchase Order
     */
    public function create(Request $request): View
    {
        $suppliers = Supplier::where('is_active', true)
            ->with(['supplierProducts.product'])
            ->orderBy('company_name')
            ->get();

        $branches = Branch::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        $selectedSupplierId = $request->input('supplier_id');

        return view('admin.procurement.create', compact('suppliers', 'branches', 'warehouses', 'selectedSupplierId'));
    }

    /**
     * Store newly created Purchase Order
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'expected_delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $po = $this->supplierService->createPurchaseOrder(
            supplierId: (int) $validated['supplier_id'],
            branchId: (int) $validated['branch_id'],
            warehouseId: (int) $validated['warehouse_id'],
            items: $validated['items'],
            expectedDate: $validated['expected_delivery_date'],
            admin: $request->user(),
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('admin.procurement.show', $po)
            ->with('success', "Purchase Order {$po->po_number} created successfully.");
    }

    /**
     * Show Purchase Order details, goods receipts, and invoices
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'branch',
            'createdBy',
            'items.product',
            'goodsReceipts.receivedBy',
            'goodsReceipts.items.product',
            'invoices',
        ]);

        return view('admin.procurement.show', compact('purchaseOrder'));
    }

    /**
     * Show Goods Receipt Note (GRN) creation form
     */
    public function createGrn(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (in_array($purchaseOrder->status, ['completed', 'cancelled'])) {
            return redirect()->route('admin.procurement.show', $purchaseOrder)
                ->with('error', "Purchase Order is already {$purchaseOrder->status}. No further goods receipts can be recorded.");
        }

        $purchaseOrder->load(['supplier', 'warehouse', 'items.product']);

        return view('admin.procurement.grn', compact('purchaseOrder'));
    }

    /**
     * Record Goods Receipt Note (GRN) with accepted vs damaged quantities
     */
    public function storeGrn(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (in_array($purchaseOrder->status, ['completed', 'cancelled'])) {
            return back()->with('error', "Purchase Order is already {$purchaseOrder->status}.");
        }

        $validated = $request->validate([
            'supplier_challan_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.quantity_accepted' => ['required', 'integer', 'min:0'],
            'items.*.quantity_damaged' => ['required', 'integer', 'min:0'],
        ]);

        // Verify that at least one item has accepted or damaged > 0
        $totalItemsReceived = 0;
        foreach ($validated['items'] as $item) {
            $totalItemsReceived += ((int) $item['quantity_accepted'] + (int) $item['quantity_damaged']);
        }

        if ($totalItemsReceived === 0) {
            return back()->withErrors(['items' => 'At least one item must have accepted or damaged quantity greater than 0.'])->withInput();
        }

        $grn = $this->supplierService->recordGoodsReceipt(
            po: $purchaseOrder,
            receiptItems: $validated['items'],
            staff: $request->user(),
            challanNo: $validated['supplier_challan_no'] ?? null,
            remarks: $validated['remarks'] ?? null
        );

        return redirect()->route('admin.procurement.show', $purchaseOrder)
            ->with('success', "Goods Receipt Note {$grn->grn_number} recorded successfully. Supplier invoice and ledger updated.");
    }
}
