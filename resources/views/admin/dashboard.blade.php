@extends('layouts.admin')

@section('title', 'Admin Operations Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Top Welcome & Date Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Operations Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time overview for {{ session('active_branch_name', 'Central Chennai Hub') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                {{ now()->format('l, d M Y') }}
            </span>
        </div>
    </div>

    <!-- KPI Metric Cards (8 Core Business Indicators) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Today's Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Orders</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-2">0</p>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span class="font-bold text-slate-700">0</span> pending dispatch
            </div>
        </div>

        <!-- Today's Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Sales</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-2">₹0.00</p>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span>0 payments received</span>
            </div>
        </div>

        <!-- Outstanding Customer Dues -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Customer Dues</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-2">₹0.00</p>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span class="text-amber-700 font-semibold">Ledger reconciled</span>
            </div>
        </div>

        <!-- Empty Cans in Circulation -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Empty Cans Held</span>
                <span class="p-2 rounded-xl bg-cyan-50 text-cyan-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-2">0</p>
            <div class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span>Total 20L/10L containers out</span>
            </div>
        </div>
    </div>

    <!-- Quick Operations Navigation Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="#" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-brand-500 hover:shadow-md transition-all text-center group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-2 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-800 block">New Customer Order</span>
        </a>

        <a href="#" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-brand-500 hover:shadow-md transition-all text-center group">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center mx-auto mb-2 group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-800 block">Raise Supplier PO</span>
        </a>

        <a href="#" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-brand-500 hover:shadow-md transition-all text-center group">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-800 block">Reconcile Staff Cash</span>
        </a>

        <a href="#" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-brand-500 hover:shadow-md transition-all text-center group">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-2 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <span class="text-xs font-bold text-slate-800 block">Financial Reports</span>
        </a>
    </div>

    <!-- Branch Operations & Facility Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
            Branch Infrastructure Status
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-500 font-semibold">Active Warehouses</p>
                <p class="text-lg font-bold text-slate-900 mt-1">1 Facility</p>
                <p class="text-[11px] text-slate-600 mt-0.5">Guindy Central Warehouse</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-500 font-semibold">Serviceable Delivery Zones</p>
                <p class="text-lg font-bold text-slate-900 mt-1">2 Zones (8 Pincodes)</p>
                <p class="text-[11px] text-slate-600 mt-0.5">Central Metro & South Coastal</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-500 font-semibold">Delivery Staff On Duty</p>
                <p class="text-lg font-bold text-emerald-600 mt-1">Active & Ready</p>
                <p class="text-[11px] text-slate-600 mt-0.5">1 Lead Executive Assigned</p>
            </div>
        </div>
    </div>
</div>
@endsection
