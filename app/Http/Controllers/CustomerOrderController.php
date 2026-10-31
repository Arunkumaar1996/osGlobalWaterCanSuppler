<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    /**
     * Display customer overview dashboard with recent deliveries and metrics.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $profile = $user->customerProfile ?? \App\Models\CustomerProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'branch_id' => session('active_branch_id', 1),
                'customer_type' => 'residential',
                'credit_limit' => 500.00,
                'outstanding_financial_balance' => 0.00,
                'allow_pay_later' => false,
            ]
        );

        $recentOrders = Order::with(['items.product', 'deliverySlot'])
            ->where('customer_profile_id', $profile->id)
            ->latest('id')
            ->take(5)
            ->get();

        $activeDeliveriesCount = Order::where('customer_profile_id', $profile->id)
            ->whereIn('delivery_status', ['pending', 'assigned', 'out_for_delivery'])
            ->count();

        return view('customer.dashboard', compact('profile', 'recentOrders', 'activeDeliveriesCount'));
    }

    /**
     * Display customer order history.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->customerProfile ?? \App\Models\CustomerProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'branch_id' => session('active_branch_id', 1),
                'customer_type' => 'residential',
                'credit_limit' => 500.00,
                'outstanding_financial_balance' => 0.00,
                'allow_pay_later' => false,
            ]
        );

        $orders = Order::with(['items.product', 'deliverySlot'])
            ->where('customer_profile_id', $profile->id)
            ->latest('id')
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * View detailed order tracking and receipt.
     */
    public function show(Request $request, Order $order): View
    {
        $user = $request->user();
        $profile = $user->customerProfile;

        if (! $profile || $order->customer_profile_id !== $profile->id) {
            abort(403, 'Unauthorized order access.');
        }

        $order->load(['items.product.canType', 'deliverySlot', 'statusHistories.changedBy']);

        return view('customer.orders.show', compact('order'));
    }
}
