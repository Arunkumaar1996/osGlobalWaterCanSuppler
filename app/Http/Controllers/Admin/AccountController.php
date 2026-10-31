<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Accounts Overview & Customer Financial Receivables Directory.
     */
    public function index(Request $request): View
    {
        $branchId = session('active_branch_id', 1);

        // Core Accounting Metrics
        $totalReceivables = CustomerProfile::sum('outstanding_financial_balance');
        $todayCollections = Payment::whereDate('payment_date', Carbon::today())
            ->where('status', 'successful')
            ->sum('amount');
        $monthCollections = Payment::whereYear('payment_date', Carbon::now()->year)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->where('status', 'successful')
            ->sum('amount');
        $payLaterCustomers = CustomerProfile::where('allow_pay_later', true)->count();
        $totalCreditLimit = CustomerProfile::where('allow_pay_later', true)->sum('credit_limit');

        // Customer Accounts Query
        $query = CustomerProfile::with(['user', 'branch'])
            ->latest('outstanding_financial_balance');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->input('filter') === 'with_dues') {
            $query->where('outstanding_financial_balance', '>', 0);
        } elseif ($request->input('filter') === 'pay_later') {
            $query->where('allow_pay_later', true);
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('admin.accounts.index', compact(
            'customers',
            'totalReceivables',
            'todayCollections',
            'monthCollections',
            'payLaterCustomers',
            'totalCreditLimit'
        ));
    }

    /**
     * Customer Individual Financial Passbook & Detailed Double-Entry Ledger.
     */
    public function customerLedger(CustomerProfile $customer): View
    {
        $customer->load(['user', 'branch', 'defaultAddress']);

        $entries = $customer->ledger()
            ->latest('id')
            ->paginate(25);

        $recentPayments = $customer->payments()
            ->with('collectedBy')
            ->latest('id')
            ->take(10)
            ->get();

        $unpaidOrders = Order::where('customer_profile_id', $customer->id)
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->latest('id')
            ->get();

        return view('admin.accounts.customer', compact(
            'customer',
            'entries',
            'recentPayments',
            'unpaidOrders'
        ));
    }

    /**
     * Record a manual payment collection (Cash, UPI, Cheque, Bank Transfer).
     */
    public function recordCollection(Request $request, CustomerProfile $customer): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,bank_transfer,cheque'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $order = null;
        if (! empty($validated['order_id'])) {
            $order = Order::where('id', $validated['order_id'])
                ->where('customer_profile_id', $customer->id)
                ->first();
        }

        $payment = $this->paymentService->recordPayment(
            customer: $customer,
            amount: (float) $validated['amount'],
            paymentMethod: $validated['payment_method'],
            order: $order,
            transactionRef: $validated['transaction_reference'] ?? null,
            collectedBy: $request->user(),
            notes: $validated['notes'] ?? ('Collection recorded by ' . $request->user()->name)
        );

        return back()->with('success', "Payment receipt #{$payment->payment_number} for ₹" . number_format($validated['amount'], 2) . " recorded and customer ledger credited.");
    }

    /**
     * Record manual credit or debit balance adjustments (waivers, bad debt, dispute fixes).
     */
    public function adjustBalance(Request $request, CustomerProfile $customer): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'adjustment_type' => ['required', 'in:credit,debit'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->paymentService->recordAdjustment(
            customer: $customer,
            amount: (float) $validated['amount'],
            adjustmentType: $validated['adjustment_type'],
            admin: $request->user(),
            reason: $validated['reason']
        );

        $typeLabel = $validated['adjustment_type'] === 'credit' ? 'Credit waiver' : 'Debit charge';

        return back()->with('success', "{$typeLabel} of ₹" . number_format($validated['amount'], 2) . " successfully posted to customer ledger.");
    }

    /**
     * Audit log of all payments collected across customers.
     */
    public function paymentsList(Request $request): View
    {
        $query = Payment::with(['customer.user', 'order', 'collectedBy'])
            ->latest('id');

        if ($request->filled('method')) {
            $query->where('payment_method', $request->input('method'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->input('date_to'));
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('admin.accounts.payments', compact('payments'));
    }
}
