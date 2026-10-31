@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Operating Expenses & Overheads</h1>
            <p class="text-sm text-slate-500 mt-1">Track plant utilities, fuel, vehicle servicing, salaries, and daily maintenance costs.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.export-expenses', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.expenses.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Record Expense
            </a>
        </div>
    </div>

    <!-- Total Sum Badge & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Filtered Period Total</p>
                <p class="text-3xl font-extrabold text-rose-600 mt-1">₹{{ number_format($totalAmount, 2) }}</p>
            </div>
            <div class="text-xs text-slate-500">
                Period: <span class="font-bold text-slate-800">{{ date('d M, Y', strtotime($startDate)) }} &rarr; {{ date('d M, Y', strtotime($endDate)) }}</span>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Expense Category</label>
                <select name="category_id" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Branch Hub</label>
                <select name="branch_id" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.expenses.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($expenses->isEmpty())
            <div class="p-12 text-center text-slate-500 text-xs">
                No expense entries found for the selected filter period.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Expense #</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5">Title & Description</th>
                            <th class="px-6 py-3.5">Paid To</th>
                            <th class="px-6 py-3.5">Payment Mode</th>
                            <th class="px-6 py-3.5 text-right">Amount</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($expenses as $exp)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-slate-900">{{ $exp->expense_number }}</td>
                                <td class="px-6 py-3.5">{{ $exp->expense_date->format('d M, Y') }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $exp->category?->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="font-bold text-slate-800">{{ $exp->title }}</p>
                                    @if($exp->notes)
                                        <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ $exp->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-slate-700">{{ $exp->paid_to ?? '—' }}</td>
                                <td class="px-6 py-3.5 font-medium uppercase text-slate-600">{{ $exp->payment_method }}</td>
                                <td class="px-6 py-3.5 text-right font-bold text-rose-600 text-sm">₹{{ number_format($exp->amount, 2) }}</td>
                                <td class="px-6 py-3.5 text-right">
                                    <form action="{{ route('admin.expenses.destroy', $exp) }}" method="POST" onsubmit="return confirm('Delete expense entry #{{ $exp->expense_number }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold text-xs">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $expenses->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
