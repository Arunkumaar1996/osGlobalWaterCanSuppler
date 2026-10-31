@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div>
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('admin.expenses.index') }}" class="hover:text-brand-600">Expenses</a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-semibold">Record</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Record Operating Expense</h1>
        <p class="text-sm text-slate-500 mt-1">Log fleet fuel, facility power/rent, staff daily wages, or maintenance bills.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <form action="{{ route('admin.expenses.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Branch Hub *</label>
                    <select name="branch_id" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500" required>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Expense Category *</label>
                    <select name="expense_category_id" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Expense Title / Item Description *</label>
                <input type="text" name="title" placeholder="e.g. Diesel fuel refill for delivery van TN-09-CB-4521" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Amount (₹ INR) *</label>
                    <input type="number" step="0.01" name="amount" placeholder="0.00" min="0.50" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Date Incurred *</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Payment Method *</label>
                    <select name="payment_method" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500" required>
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / GPay</option>
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="card">Company Debit/Credit Card</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Paid To / Vendor Name</label>
                    <input type="text" name="paid_to" placeholder="e.g. Indian Oil Fuel Station, Indian Power Corp" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Bill / Receipt / Reference #</label>
                    <input type="text" name="reference_invoice_no" placeholder="e.g. INV-2026-9041" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Additional Notes / Particulars</label>
                <textarea name="notes" rows="3" placeholder="Optional notes for accounting audit..." class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.expenses.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-colors">
                    Save Expense Entry
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
