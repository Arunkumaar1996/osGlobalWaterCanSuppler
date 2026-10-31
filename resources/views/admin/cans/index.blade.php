@extends('layouts.admin')

@section('title', 'Empty-Can Exchanges & Fleet Management')

@section('page_title', 'Empty-Can Exchanges & Container Fleet')

@section('page_actions')
<a href="{{ route('admin.cans.movements') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Container Movements Audit Log
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Top KPI Container Fleet Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Circulating Bottles -->
        <div class="bg-gradient-to-br from-cyan-600 to-blue-700 text-white p-5 rounded-3xl shadow-lg flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-cyan-100">Containers in Circulation</span>
                <p class="text-3xl font-black mt-1">{{ number_format($totalCirculating) }} <span class="text-xs font-sans font-semibold text-cyan-200">Cans</span></p>
                <span class="text-[11px] text-cyan-100 mt-0.5 block">Held across all customer locations</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
        </div>

        <!-- Active Holding Customers -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Holding Customers</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($customersWithCans) }}</p>
                <span class="text-[11px] font-semibold text-slate-500 mt-0.5 block">Premises with business bottles</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- 20L Premium Cans Fleet -->
        @php
            $twentyLitre = $canTypesSummary->firstWhere('capacity_litres', 20.00);
            $otherSizes = $canTypesSummary->where('capacity_litres', '!=', 20.00);
        @endphp
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $twentyLitre?->name ?? '20L Cans' }}</span>
                <p class="text-2xl font-black text-brand-600 mt-1">{{ number_format($twentyLitre?->customer_balances_sum_held_cans_count ?? 0) }}</p>
                <span class="text-[11px] font-semibold text-slate-500 mt-0.5 block">Standard bubble tops</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>

        <!-- Smaller Sizes Fleet -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Other Sizes (10L / 5L)</span>
                <p class="text-2xl font-black text-purple-600 mt-1">{{ number_format($otherSizes->sum('customer_balances_sum_held_cans_count')) }}</p>
                <span class="text-[11px] font-semibold text-slate-500 mt-0.5 block">Dispenser & table sizes</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>
    </div>

    <!-- Segregation Notice Banner -->
    <div class="p-4 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-between text-xs text-slate-700">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-600"></span>
            <span class="font-bold">Strict Segregation Rule:</span>
            <span>Container bottles are tracked on an exchange inventory ledger, never combined with customer monetary ledger balances.</span>
        </div>
        <a href="{{ route('admin.accounts.index') }}" class="font-bold text-brand-600 hover:underline">
            View Financial Accounts &rarr;
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.cans.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-grow w-full">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer by name or mobile number..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>

            <select name="filter" class="w-full sm:w-56 py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500">
                <option value="">All Customer Records</option>
                <option value="holding_cans" {{ request('filter') === 'holding_cans' ? 'selected' : '' }}>Currently Holding Cans (> 0)</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition-colors">
                Apply Filters
            </button>

            @if(request()->anyFilled(['search', 'filter']))
                <a href="{{ route('admin.cans.index') }}" class="w-full sm:w-auto px-3 py-2 text-center text-xs font-bold text-slate-500 hover:text-slate-800">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Customer Containers Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Customer Details</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4 text-center">Total Cans Held</th>
                        <th class="py-3 px-4">Holdings by Can Type</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        @php
                            $totalHeld = (int) ($customer->can_balances_sum_held_cans_count ?? 0);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $customer->user?->name }}</div>
                                <div class="text-slate-500 text-[11px]">{{ $customer->user?->mobile }} &bull; {{ $customer->customer_type }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">
                                {{ $customer->branch?->name ?? 'Headquarters' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-black {{ $totalHeld > 0 ? 'bg-cyan-100 text-cyan-800 font-mono' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $totalHeld }} cans
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($customer->canBalances->where('held_cans_count', '>', 0) as $b)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $b->canType?->name }}: <strong class="text-slate-900">{{ $b->held_cans_count }}</strong>
                                        </span>
                                    @empty
                                        <span class="text-slate-400 italic text-[11px]">No bottles currently held</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('admin.cans.customer', $customer->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Manage & Passbook
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                No customer container accounts found.
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
