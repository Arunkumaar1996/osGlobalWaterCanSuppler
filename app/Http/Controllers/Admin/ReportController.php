<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CustomerProfile;
use App\Models\Expense;
use App\Models\Order;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Executive Overview & Business Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $branchId = $request->query('branch_id');
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $summary = $this->analyticsService->getExecutiveSummary($branchId ? (int) $branchId : null, $startDate, $endDate);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.reports.index', compact('summary', 'branches', 'branchId', 'startDate', 'endDate'));
    }

    /**
     * Profit & Loss (P&L) Statement.
     */
    public function pnl(Request $request): View
    {
        $branchId = $request->query('branch_id');
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $pnl = $this->analyticsService->getProfitAndLossStatement($branchId ? (int) $branchId : null, $startDate, $endDate);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.reports.pnl', compact('pnl', 'branches', 'branchId', 'startDate', 'endDate'));
    }

    /**
     * Container Fleet & Shrinkage Report.
     */
    public function containers(Request $request): View
    {
        $branchId = $request->query('branch_id');
        $report = $this->analyticsService->getContainerFleetReport($branchId ? (int) $branchId : null);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.reports.containers', compact('report', 'branches', 'branchId'));
    }

    /**
     * Delivery Fleet & Staff Driver Efficiency Metrics.
     */
    public function fleet(Request $request): View
    {
        $branchId = $request->query('branch_id');
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $efficiency = $this->analyticsService->getFleetEfficiencyReport($branchId ? (int) $branchId : null, $startDate, $endDate);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.reports.fleet', compact('efficiency', 'branches', 'branchId', 'startDate', 'endDate'));
    }

    /**
     * Stream CSV Export of Orders.
     */
    public function exportOrders(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $branchId = $request->query('branch_id');

        $query = Order::with(['customer.user', 'branch'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->latest('id');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"orders_{$startDate}_to_{$endDate}.csv\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number',
                'Date',
                'Customer Name',
                'Customer Mobile',
                'Branch',
                'Status',
                'Delivery Status',
                'Payment Status',
                'Subtotal (INR)',
                'Tax (INR)',
                'Delivery Fee (INR)',
                'Total Amount (INR)',
            ]);

            $query->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->customer?->user?->name ?? 'N/A',
                        $order->customer?->user?->mobile ?? 'N/A',
                        $order->branch?->name ?? 'N/A',
                        $order->order_status,
                        $order->delivery_status,
                        $order->payment_status,
                        number_format($order->subtotal_amount, 2, '.', ''),
                        number_format($order->tax_amount, 2, '.', ''),
                        number_format($order->delivery_charge, 2, '.', ''),
                        number_format($order->total_amount, 2, '.', ''),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Stream CSV Export of Expenses.
     */
    public function exportExpenses(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $branchId = $request->query('branch_id');

        $query = Expense::with(['category', 'branch', 'recordedBy'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest('expense_date');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"expenses_{$startDate}_to_{$endDate}.csv\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Expense Number',
                'Date',
                'Category',
                'Title',
                'Amount (INR)',
                'Payment Method',
                'Paid To',
                'Reference Bill/Invoice',
                'Branch',
                'Recorded By',
                'Notes',
            ]);

            $query->chunk(200, function ($expenses) use ($handle) {
                foreach ($expenses as $expense) {
                    fputcsv($handle, [
                        $expense->expense_number,
                        $expense->expense_date->format('Y-m-d'),
                        $expense->category?->name ?? 'N/A',
                        $expense->title,
                        number_format($expense->amount, 2, '.', ''),
                        $expense->payment_method,
                        $expense->paid_to ?? '',
                        $expense->reference_invoice_no ?? '',
                        $expense->branch?->name ?? 'N/A',
                        $expense->recordedBy?->name ?? 'N/A',
                        $expense->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Stream CSV Export of Customer Outstanding Dues.
     */
    public function exportDues(): StreamedResponse
    {
        $query = CustomerProfile::with('user', 'branch')
            ->where('outstanding_balance', '>', 0)
            ->orderBy('outstanding_balance', 'desc');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customer_outstanding_dues_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Customer Name',
                'Mobile',
                'Email',
                'Customer Type',
                'Branch',
                'Credit Limit (INR)',
                'Outstanding Balance (INR)',
                'Pay Later Enabled',
            ]);

            $query->chunk(200, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    fputcsv($handle, [
                        $c->user?->name ?? 'N/A',
                        $c->user?->mobile ?? 'N/A',
                        $c->user?->email ?? 'N/A',
                        $c->customer_type,
                        $c->branch?->name ?? 'N/A',
                        number_format($c->credit_limit, 2, '.', ''),
                        number_format($c->outstanding_balance, 2, '.', ''),
                        $c->pay_later_approved ? 'Yes' : 'No',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
