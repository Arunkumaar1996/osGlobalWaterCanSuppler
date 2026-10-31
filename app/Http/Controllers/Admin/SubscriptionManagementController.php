<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionManagementController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Display all customer subscriptions with filters.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $frequency = $request->query('frequency');
        $search = $request->query('search');

        $query = Subscription::with(['customer.user', 'deliverySlot', 'deliveryAddress', 'items.product'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($frequency) {
            $query->where('frequency', $frequency);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subscription_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer.user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('mobile', 'LIKE', "%{$search}%");
                  });
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'paused' => Subscription::where('status', 'paused')->count(),
            'due_tomorrow' => Subscription::where('status', 'active')
                ->whereDate('next_delivery_date', '<=', Carbon::tomorrow()->format('Y-m-d'))
                ->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'status', 'frequency', 'search'));
    }

    /**
     * Show subscription details.
     */
    public function show(Subscription $subscription): View
    {
        $subscription->load([
            'customer.user',
            'deliveryAddress',
            'deliverySlot',
            'items.product.canType',
            'orders' => fn($q) => $q->latest('id')->limit(15),
        ]);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Manual Trigger to generate scheduled recurring orders for a given date.
     */
    public function generateManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_date' => ['required', 'date'],
        ]);

        $targetDate = $validated['target_date'];
        $generated = $this->subscriptionService->generateScheduledOrders($targetDate);

        return back()->with('success', "Processed recurring subscriptions for {$targetDate}: Generated {$generated} order(s).");
    }
}
