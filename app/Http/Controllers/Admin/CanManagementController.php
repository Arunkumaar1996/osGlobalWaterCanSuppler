<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CanMovement;
use App\Models\CanType;
use App\Models\CustomerCanBalance;
use App\Models\CustomerProfile;
use App\Services\CanExchangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CanManagementController extends Controller
{
    public function __construct(
        protected CanExchangeService $exchangeService
    ) {}

    /**
     * Empty-Can Exchanges & Circulating Container Fleet Overview.
     */
    public function index(Request $request): View
    {
        $branchId = session('active_branch_id', 1);

        // Core Fleet Metrics
        $totalCirculating = CustomerCanBalance::sum('held_cans_count');
        $canTypesSummary = CanType::where('is_active', true)
            ->withSum('customerBalances', 'held_cans_count')
            ->get();
        $customersWithCans = CustomerCanBalance::where('held_cans_count', '>', 0)
            ->distinct('customer_profile_id')
            ->count('customer_profile_id');

        // Customer Container Accounts Query
        $query = CustomerProfile::with(['user', 'branch', 'canBalances.canType'])
            ->withSum('canBalances', 'held_cans_count')
            ->latest('can_balances_sum_held_cans_count');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->input('filter') === 'holding_cans') {
            $query->having('can_balances_sum_held_cans_count', '>', 0);
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('admin.cans.index', compact(
            'customers',
            'totalCirculating',
            'canTypesSummary',
            'customersWithCans'
        ));
    }

    /**
     * Customer Individual Container Holding Statement & Action Console.
     */
    public function customerBalance(CustomerProfile $customer): View
    {
        $customer->load(['user', 'branch', 'defaultAddress']);

        $balances = $this->exchangeService->getCustomerCanBalances($customer);
        $totalHeld = $this->exchangeService->getCustomerTotalHeldCans($customer);
        $canTypes = CanType::where('is_active', true)->get();

        $movements = $customer->canMovements()
            ->with(['canType', 'recordedBy'])
            ->latest('id')
            ->paginate(20);

        return view('admin.cans.customer', compact(
            'customer',
            'balances',
            'totalHeld',
            'canTypes',
            'movements'
        ));
    }

    /**
     * Record an empty container return collected from customer.
     */
    public function recordReturn(Request $request, CustomerProfile $customer): RedirectResponse
    {
        $validated = $request->validate([
            'can_type_id' => ['required', 'exists:can_types,id'],
            'collected_empty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $canType = CanType::findOrFail($validated['can_type_id']);

        $this->exchangeService->recordExchange(
            customer: $customer,
            canType: $canType,
            deliveredFull: 0,
            collectedEmpty: (int) $validated['collected_empty'],
            order: null,
            recordedBy: $request->user(),
            notes: $validated['notes'] ?? ("Returned {$validated['collected_empty']}x {$canType->name} directly to hub")
        );

        return back()->with('success', "Collected {$validated['collected_empty']} empty {$canType->name} containers. Customer container holding balance reduced.");
    }

    /**
     * Record missing/broken container penalty: reduces container holding
     * and automatically posts monetary debit adjustment to customer financial ledger.
     */
    public function chargePenalty(Request $request, CustomerProfile $customer): RedirectResponse
    {
        $validated = $request->validate([
            'can_type_id' => ['required', 'exists:can_types,id'],
            'missing_cans' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $canType = CanType::findOrFail($validated['can_type_id']);

        $result = $this->exchangeService->recordMissingPenalty(
            customer: $customer,
            canType: $canType,
            missingCount: (int) $validated['missing_cans'],
            admin: $request->user(),
            reason: $validated['reason']
        );

        return back()->with('success', "Penalty of ₹" . number_format($result['penalty_amount'], 2) . " billed to customer financial ledger for {$validated['missing_cans']} missing {$canType->name} containers.");
    }

    /**
     * Reconcile customer physical count against on-site audit.
     */
    public function reconcile(Request $request, CustomerProfile $customer): RedirectResponse
    {
        $validated = $request->validate([
            'can_type_id' => ['required', 'exists:can_types,id'],
            'actual_count' => ['required', 'integer', 'min:0'],
            'notes' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $canType = CanType::findOrFail($validated['can_type_id']);

        $this->exchangeService->reconcileBalance(
            customer: $customer,
            canType: $canType,
            actualPhysicalCount: (int) $validated['actual_count'],
            admin: $request->user(),
            notes: $validated['notes']
        );

        return back()->with('success', "Reconciled {$canType->name} physical balance to {$validated['actual_count']} cans.");
    }

    /**
     * System-wide physical container movement audit log.
     */
    public function movementsAudit(Request $request): View
    {
        $query = CanMovement::with(['customer.user', 'canType', 'recordedBy'])
            ->latest('id');

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }

        if ($request->filled('can_type_id')) {
            $query->where('can_type_id', $request->input('can_type_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $movements = $query->paginate(20)->withQueryString();
        $canTypes = CanType::where('is_active', true)->get();

        return view('admin.cans.movements', compact('movements', 'canTypes'));
    }
}
