<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\FulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected FulfillmentService $fulfillmentService
    ) {}

    /**
     * Display listing of customer orders with independent status filters
     */
    public function index(Request $request): View
    {
        $query = Order::with(['customer.user', 'deliverySlot', 'items.product'])
            ->latest('id');

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'LIKE', $term)
                  ->orWhereHas('customer.user', function ($uq) use ($term) {
                      $uq->where('name', 'LIKE', $term)
                         ->orWhere('mobile', 'LIKE', $term)
                         ->orWhere('email', 'LIKE', $term);
                  });
            });
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->input('order_status'));
        }

        if ($request->filled('fulfillment_status')) {
            $query->where('fulfillment_status', $request->input('fulfillment_status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->input('delivery_status'));
        }

        if ($request->filled('delivery_date')) {
            $query->whereDate('delivery_date', $request->input('delivery_date'));
        }

        $orders = $query->paginate(15)->withQueryString();

        // Status counts for quick dashboard pills
        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('order_status', 'pending')->count(),
            'unfulfilled' => Order::where('fulfillment_status', 'unfulfilled')->count(),
            'out_for_delivery' => Order::where('delivery_status', 'out_for_delivery')->count(),
            'delivered' => Order::where('delivery_status', 'delivered')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'counts'));
    }

    /**
     * Display order hub with split-fulfillment sourcing engine
     */
    public function show(Order $order): View
    {
        $order->load([
            'customer.user',
            'branch',
            'deliverySlot',
            'items.product.category',
            'statusHistories.changedBy',
            'fulfillments.warehouse',
            'fulfillments.supplier',
            'fulfillments.items.product',
        ]);

        $warehouses = Warehouse::where('branch_id', $order->branch_id)
            ->where('is_active', true)
            ->with(['stocks' => fn($q) => $q->whereIn('product_id', $order->items->pluck('product_id'))])
            ->get();

        $suppliers = Supplier::where('is_active', true)
            ->with(['supplierProducts' => fn($q) => $q->whereIn('product_id', $order->items->pluck('product_id'))])
            ->get();

        return view('admin.orders.show', compact('order', 'warehouses', 'suppliers'));
    }

    /**
     * Update order status independently
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status_type' => ['required', 'string', 'in:order_status,delivery_status,payment_status'],
            'new_status' => ['required', 'string'],
            'comment' => ['nullable', 'string', 'max:255'],
        ]);

        $field = $validated['status_type'];
        $oldValue = $order->$field;
        $newValue = $validated['new_status'];

        $order->update([$field => $newValue]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $oldValue,
            'to_status' => $newValue,
            'comment' => ($validated['comment'] ?? 'Status updated manually') . " ({$field})",
            'changed_by_user_id' => $request->user()->id,
        ]);

        return back()->with('success', "Order {$field} updated to '{$newValue}'.");
    }

    /**
     * Cancel order completely
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->fulfillmentService->cancelOrder($order, $request->user(), $validated['reason']);

        return back()->with('success', "Order {$order->order_number} has been cancelled and reservations released.");
    }
}
