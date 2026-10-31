@extends('layouts.admin')

@section('title', 'Customer Accounts & Financial Ledgers')

@section('page_title', 'Customer Financial Accounts & Receivables')

@section('page_actions')
<a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    All Payments Audit Log
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Top KPI Accounting Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Receivables -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Receivables</span>
                <p class="text-2xl font-black text-rose-600 mt-1">₹{{ number_format($totalReceivables, 2) }}</p>
                <span class="text-[11px] font-semibold text-slate-500 mt-0.5 block">Live customer balance dues</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Today Collections -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Collections Today</span>
                <p class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($todayCollections, 2) }}</p>
                <span class="text-[11px] font-semibold text-slate-500 mt-0.5 block">{{ date('d M Y') }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Monthly Collections -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Month to Date</span>
                <p class="text-2xl font-black text-brand-600 mt-1">₹{{ number_format($monthCollections, 2) }}</p>
                <span class="text-[11px] font-semibold text-slate-500 mt-0.5 block">{{ date('F Y') }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <!-- Pay Later Exposure -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Credit Exposure</span>
                <p class="text-2xl font-black text-purple-600 mt-1">₹{{ number_format($totalCreditLimit, 2) }}</p>
                <span class="text-[11px] font-semibold text-slate-500 mt-0.5 block">{{ $payLaterCustomers }} Active Credit Accounts</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.accounts.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-grow w-full">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer by name, mobile, or email..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>

            <select name="filter" class="w-full sm:w-56 py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500">
                <option value="">All Customer Accounts</option>
                <option value="with_dues" {{ request('filter') === 'with_dues' ? 'selected' : '' }}>Outstanding Dues (> ₹0)</option>
                <option value="pay_later" {{ request('filter') === 'pay_later' ? 'selected' : '' }}>Pay Later Approved</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition-colors">
                Apply Filters
            </button>

            @if(request()->anyFilled(['search', 'filter']))
                <a href="{{ route('admin.accounts.index') }}" class="w-full sm:w-auto px-3 py-2 text-center text-xs font-bold text-slate-500 hover:text-slate-800">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Customers Accounts Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Customer Details</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Credit Limit</th>
                        <th class="py-3 px-4">Pay Later Status</th>
                        <th class="py-3 px-4 text-right">Outstanding Due</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $customer->user?->name }}</div>
                                <div class="text-slate-500 text-[11px]">{{ $customer->user?->mobile }} &bull; {{ $customer->user?->email }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">
                                {{ $customer->branch?->name ?? 'Headquarters' }}
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                ₹{{ number_format($customer->credit_limit, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                @if($customer->allow_pay_later)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Approved
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Prepaid Only
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <span class="font-mono text-sm font-black {{ $customer->outstanding_financial_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    ₹{{ number_format($customer->outstanding_financial_balance, 2) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('admin.accounts.customer', $customer->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Ledger & Collect
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                No customer accounts found matching search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
