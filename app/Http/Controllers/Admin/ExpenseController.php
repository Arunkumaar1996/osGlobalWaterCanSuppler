<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * List all business operating expenses with filters.
     */
    public function index(Request $request): View
    {
        $branchId = $request->query('branch_id');
        $categoryId = $request->query('category_id');
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $query = Expense::with(['category', 'branch', 'recordedBy'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest('expense_date');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $expenses = $query->paginate(25)->withQueryString();
        $totalAmount = (float) (clone $query)->sum('amount');

        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.expenses.index', compact(
            'expenses',
            'categories',
            'branches',
            'branchId',
            'categoryId',
            'startDate',
            'endDate',
            'totalAmount'
        ));
    }

    /**
     * Show form to record a new business expense.
     */
    public function create(): View
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.expenses.create', compact('categories', 'branches'));
    }

    /**
     * Store new expense.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0.50'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,upi,bank_transfer,cheque,card'],
            'paid_to' => ['nullable', 'string', 'max:150'],
            'reference_invoice_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $year = date('Y');
        $latest = Expense::where('expense_number', 'LIKE', "EXP-{$year}-%")->latest('id')->first();
        $seq = $latest ? ((int) substr($latest->expense_number, -4)) + 1 : 1;
        $expenseNumber = sprintf("EXP-%s-%04d", $year, $seq);

        Expense::create([
            'expense_number' => $expenseNumber,
            'branch_id' => $validated['branch_id'],
            'expense_category_id' => $validated['expense_category_id'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'paid_to' => $validated['paid_to'] ?? null,
            'reference_invoice_no' => $validated['reference_invoice_no'] ?? null,
            'recorded_by_user_id' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Expense {$expenseNumber} for ₹" . number_format($validated['amount'], 2) . " recorded successfully.");
    }

    /**
     * Remove an erroneous expense.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $num = $expense->expense_number;
        $expense->delete();

        return back()->with('success', "Expense {$num} deleted.");
    }
}
