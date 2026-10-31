<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DispatchManifest;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\Warehouse;
use App\Services\FulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FulfillmentController extends Controller
{
    public function __construct(
        protected FulfillmentService $fulfillmentService
    ) {}

    /**
     * Allocate items of an order to a warehouse or supplier
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'source_type' => ['required', 'string', 'in:warehouse,supplier'],
            'source_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        // Filter out zero quantities
        $allocations = array_filter($validated['items'], fn($item) => (int)$item['quantity'] > 0);

        if (empty($allocations)) {
            return back()->with('error', 'Please allocate a quantity greater than zero for at least one item.')->withInput();
        }

        try {
            $fulfillment = $this->fulfillmentService->allocateFulfillment(
                order: $order,
                sourceType: $validated['source_type'],
                sourceId: (int) $validated['source_id'],
                allocations: $allocations,
                user: $request->user(),
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', "Fulfillment {$fulfillment->fulfillment_number} allocated successfully.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Dispatch an allocated fulfillment
     */
    public function dispatch(Request $request, OrderFulfillment $fulfillment): RedirectResponse
    {
        try {
            $this->fulfillmentService->dispatchFulfillment($fulfillment, $request->user());
            return back()->with('success', "Fulfillment {$fulfillment->fulfillment_number} marked as dispatched.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel an active fulfillment
     */
    public function cancel(Request $request, OrderFulfillment $fulfillment): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->fulfillmentService->cancelFulfillment($fulfillment, $request->user(), $validated['reason']);
            return back()->with('success', "Fulfillment {$fulfillment->fulfillment_number} cancelled.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * List all dispatch manifests
     */
    public function manifestsIndex(Request $request): View
    {
        $query = DispatchManifest::with(['branch', 'warehouse', 'generatedBy', 'fulfillments'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $manifests = $query->paginate(15)->withQueryString();

        return view('admin.fulfillments.manifests.index', compact('manifests'));
    }

    /**
     * Show form to generate a new dispatch manifest
     */
    public function manifestsCreate(Request $request): View
    {
        $branches = Branch::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        // Get fulfillments that are allocated and ready to be loaded onto a truck
        $fulfillments = OrderFulfillment::whereIn('status', ['allocated', 'ready_for_dispatch'])
            ->with(['order.customer.user', 'items.product', 'warehouse', 'supplier'])
            ->latest('id')
            ->get();

        return view('admin.fulfillments.manifests.create', compact('branches', 'warehouses', 'fulfillments'));
    }

    /**
     * Store and generate dispatch manifest
     */
    public function manifestsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:255'],
            'fulfillments' => ['required', 'array', 'min:1'],
            'fulfillments.*' => ['exists:order_fulfillments,id'],
        ]);

        $manifest = $this->fulfillmentService->generateManifest(
            branchId: (int) $validated['branch_id'],
            warehouseId: $validated['warehouse_id'] ? (int) $validated['warehouse_id'] : null,
            fulfillmentIds: $validated['fulfillments'],
            vehicle: $validated['vehicle_number'] ?? null,
            driverName: $validated['driver_name'] ?? null,
            driverPhone: $validated['driver_phone'] ?? null,
            user: $request->user(),
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('admin.manifests.show', $manifest)
            ->with('success', "Dispatch Manifest {$manifest->manifest_number} generated successfully.");
    }

    /**
     * Display printable dispatch manifest / packing slip
     */
    public function manifestsShow(DispatchManifest $manifest): View
    {
        $manifest->load([
            'branch',
            'warehouse',
            'generatedBy',
            'fulfillments.order.customer.user',
            'fulfillments.order.deliverySlot',
            'fulfillments.items.product',
        ]);

        return view('admin.fulfillments.manifests.show', compact('manifest'));
    }
}
