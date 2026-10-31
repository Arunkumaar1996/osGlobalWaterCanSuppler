@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('admin.reports.index') }}" class="hover:text-brand-600">Analytics</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Container Asset Audit</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Container Fleet & Shrinkage Audit</h1>
            <p class="text-sm text-slate-500 mt-1">Physical water bottle inventory audit, customer circulation, broken units, and shrinkage impact.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.cans.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                <span>Manage Empty Exchanges &rarr;</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                Back to Analytics
            </a>
        </div>
    </div>

    <!-- Branch Filter -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.containers') }}" class="flex items-center gap-3">
            <div class="w-64">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Branch Hub</label>
                <select name="branch_id" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Branches (Consolidated)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pt-5">
                <button type="submit" class="py-1.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Shrinkage & High-Level Asset Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Fleet -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Active Fleet</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($report['total_fleet_size']) }} cans</p>
            <p class="text-xs text-slate-400 mt-1">Warehouse + Customer</p>
        </div>

        <!-- In Customer Circulation -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Customer Hands</p>
            <p class="text-2xl font-black text-brand-600 mt-1">{{ number_format($report['customer_holding']) }} cans</p>
            <p class="text-xs text-slate-400 mt-1">Empties due for return</p>
        </div>

        <!-- Warehouse Plant Stock -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">In Warehouse Hub</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($report['total_warehouse']) }} cans</p>
            <p class="text-xs text-slate-400 mt-1">Full, empty & cleaning</p>
        </div>

        <!-- Shrinkage Rate -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Shrinkage / Loss Rate</p>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $report['shrinkage_percent'] }}%</p>
            <p class="text-xs text-slate-400 mt-1">{{ number_format($report['lost_damaged_count']) }} lost / damaged units</p>
        </div>
    </div>

    <!-- Detailed Status Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Warehouse Stock State Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Warehouse Container State Audit</h3>
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="font-medium text-slate-700">Full Ready Cans</span>
                    </span>
                    <strong class="text-slate-900">{{ number_format($report['warehouse_full']) }}</strong>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-cyan-500"></span>
                        <span class="font-medium text-slate-700">Empty Returned Cans</span>
                    </span>
                    <strong class="text-slate-900">{{ number_format($report['warehouse_empty']) }}</strong>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="font-medium text-slate-700">Under Sanitization / Washing</span>
                    </span>
                    <strong class="text-slate-900">{{ number_format($report['under_cleaning']) }}</strong>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="font-medium text-slate-700">Warehouse Damaged / Scrapped</span>
                    </span>
                    <strong class="text-rose-600">{{ number_format($report['warehouse_damaged']) }}</strong>
                </div>
            </div>
        </div>

        <!-- Asset Valuation & Replacement Loss -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900">Financial Replacement Exposure</h3>
            <p class="text-xs text-slate-500">Container bottles represent significant physical capital. Unreturned or lost units require replacement expenditure.</p>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-600">Total Lost / Broken Cans:</span>
                    <strong class="text-slate-900">{{ number_format($report['lost_damaged_count']) }} units</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Benchmark Can Replacement Cost:</span>
                    <span class="text-slate-700">₹150.00 / can</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-sm">
                    <span class="text-slate-900">Total Asset Replacement Valuation:</span>
                    <span class="text-rose-600">₹{{ number_format($report['estimated_replacement_cost'], 2) }}</span>
                </div>
            </div>

            <div class="text-xs text-slate-400">
                Note: Missing cans charged to customer accounts offset this asset loss directly as debit ledger entries.
            </div>
        </div>
    </div>
</div>
@endsection
