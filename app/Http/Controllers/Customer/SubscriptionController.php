<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlot;
use App\Models\Product;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * List all subscriptions of the authenticated customer.
     */
    public function index(Request $request): View
    {
        $customer = $request->user()->customerProfile;

        if (! $customer) {
            abort(403, 'Customer profile required.');
        }

        $subscriptions = Subscription::with(['items.product.canType', 'deliverySlot', 'deliveryAddress'])
            ->where('customer_profile_id', $customer->id)
            ->latest('id')
            ->paginate(10);

        $activeCount = Subscription::where('customer_profile_id', $customer->id)->where('status', 'active')->count();
        $pausedCount = Subscription::where('customer_profile_id', $customer->id)->where('status', 'paused')->count();

        return view('customer.subscriptions.index', compact('customer', 'subscriptions', 'activeCount', 'pausedCount'));
    }

    /**
     * Show form to configure a new recurring water delivery subscription.
     */
    public function create(Request $request): View
    {
        $customer = $request->user()->customerProfile;

        if (! $customer) {
            abort(403, 'Customer profile required.');
        }

        $addresses = $customer->addresses()->get();
        $branchId = $customer->branch_id ?? session('active_branch_id', 1);

        $slots = DeliverySlot::where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($slots->isEmpty()) {
            $slots = DeliverySlot::where('is_active', true)->orderBy('start_time')->get();
        }

        $products = Product::where('is_active', true)
            ->with('canType')
            ->get();

        return view('customer.subscriptions.create', compact('customer', 'addresses', 'slots', 'products'));
    }

    /**
     * Store new subscription.
     */
    public function store(Request $request): RedirectResponse
    {
        $customer = $request->user()->customerProfile;

        if (! $customer) {
            abort(403, 'Customer profile required.');
        }

        $validated = $request->validate([
            'delivery_address_id' => ['required', 'integer', 'exists:customer_addresses,id'],
            'delivery_slot_id' => ['required', 'integer', 'exists:delivery_slots,id'],
            'frequency' => ['required', 'string', 'in:daily,alternate_days,weekly,monthly,custom_days'],
            'custom_days' => ['nullable', 'array'],
            'custom_days.*' => ['in:mon,tue,wed,thu,fri,sat,sun'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'payment_method' => ['required', 'string', 'in:cod,pay_later'],
            'customer_notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validated['frequency'] === 'custom_days' && empty($validated['custom_days'])) {
            return back()->withErrors(['custom_days' => 'Please select at least one day for custom schedule.'])->withInput();
        }

        $subscription = $this->subscriptionService->createSubscription(
            customer: $customer,
            addressId: (int) $validated['delivery_address_id'],
            slotId: (int) $validated['delivery_slot_id'],
            frequency: $validated['frequency'],
            items: $validated['items'],
            startDate: $validated['start_date'],
            endDate: $validated['end_date'] ?? null,
            customDays: $validated['custom_days'] ?? [],
            paymentMethod: $validated['payment_method'],
            notes: $validated['customer_notes'] ?? null
        );

        return redirect()->route('customer.subscriptions.show', $subscription)
            ->with('success', "Subscription #{$subscription->subscription_number} activated successfully! Water will be delivered as scheduled.");
    }

    /**
     * Show details of a subscription.
     */
    public function show(Request $request, Subscription $subscription): View
    {
        $customer = $request->user()->customerProfile;

        if ($subscription->customer_profile_id !== $customer?->id && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized access to subscription.');
        }

        $subscription->load([
            'items.product.canType',
            'deliverySlot',
            'deliveryAddress',
            'orders' => fn($q) => $q->latest('id')->limit(10),
        ]);

        return view('customer.subscriptions.show', compact('subscription'));
    }

    /**
     * Pause an active subscription.
     */
    public function pause(Request $request, Subscription $subscription): RedirectResponse
    {
        $customer = $request->user()->customerProfile;

        if ($subscription->customer_profile_id !== $customer?->id && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        if ($subscription->status !== 'active') {
            return back()->withErrors(['error' => 'Only active subscriptions can be paused.']);
        }

        $reason = $request->input('pause_reason', 'Paused via customer portal');
        $this->subscriptionService->pauseSubscription($subscription, $reason);

        return back()->with('success', "Subscription #{$subscription->subscription_number} paused. No orders will be scheduled until resumed.");
    }

    /**
     * Resume a paused subscription.
     */
    public function resume(Request $request, Subscription $subscription): RedirectResponse
    {
        $customer = $request->user()->customerProfile;

        if ($subscription->customer_profile_id !== $customer?->id && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        if ($subscription->status !== 'paused') {
            return back()->withErrors(['error' => 'Only paused subscriptions can be resumed.']);
        }

        $this->subscriptionService->resumeSubscription($subscription);

        return back()->with('success', "Subscription #{$subscription->subscription_number} resumed! Next delivery on " . $subscription->next_delivery_date->format('d M, Y'));
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        $customer = $request->user()->customerProfile;

        if ($subscription->customer_profile_id !== $customer?->id && ! $request->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($subscription->status, ['cancelled', 'completed'])) {
            return back()->withErrors(['error' => 'Subscription is already ended.']);
        }

        $reason = $request->input('cancel_reason', 'Cancelled via customer portal');
        $this->subscriptionService->cancelSubscription($subscription, $reason);

        return back()->with('success', "Subscription #{$subscription->subscription_number} has been cancelled.");
    }
}
