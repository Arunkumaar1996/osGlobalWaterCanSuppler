@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header with Quick Report Links & CSV Export Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Executive Business Analytics</h1>
            <p class="text-sm text-slate-500 mt-1">Consolidated revenue, operating costs, gross margins, and delivery metrics.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.reports.pnl', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <span>📊 P&L Statement</span>
            </a>
            <a href="{{ route('admin.reports.containers', ['branch_id' => $branchId]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <span>🔄 Can Asset Health</span>
            </a>
            <a href="{{ route('admin.reports.fleet', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <span>🚚 Fleet Efficiency</span>
            </a>
            <a href="{{ route('admin.reports.export-orders', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export Orders</span>
            </a>
        </div>
    </div>

    <!-- Date Range & Branch Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Branch Hub</label>
                <select name="branch_id" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Consolidated (All Branches)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors">
                    Update Report
                </button>
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Executive KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Gross Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gross Sales Revenue</p>
            <p class="text-2xl font-black text-slate-900 mt-1">₹{{ number_format($summary['gross_sales'], 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ number_format($summary['orders_count']) }} orders booked</p>
        </div>

        <!-- Total Collected -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Cash & Online Collected</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($summary['total_collected'], 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Cash: ₹{{ number_format($summary['cash_collected'], 2) }} | Online: ₹{{ number_format($summary['online_collected'], 2) }}</p>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Operating Expenses</p>
            <p class="text-2xl font-black text-rose-600 mt-1">₹{{ number_format($summary['total_expenses'], 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Procurement COGS: ₹{{ number_format($summary['procurement_cost'], 2) }}</p>
        </div>

        <!-- Net Operating Profit -->
        <div class="bg-gradient-to-br {{ $summary['net_operating_profit'] >= 0 ? 'from-emerald-500 to-teal-700' : 'from-rose-500 to-red-700' }} p-5 rounded-2xl text-white shadow-md">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Net Operating Profit</p>
            <p class="text-2xl font-black mt-1">₹{{ number_format($summary['net_operating_profit'], 2) }}</p>
            <p class="text-xs text-emerald-100 mt-1">Profit Margin: <strong>{{ $summary['profit_margin'] }}%</strong></p>
        </div>
    </div>

    <!-- Secondary Metric Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Water Volume Delivered -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900">Total Volume & Cans</h3>
                <span class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-xs">💧</span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900">{{ number_format($summary['total_cans_ordered']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Total water cans ordered in this period</p>
            <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-600 space-y-1">
                <p><strong>Delivered Orders:</strong> {{ number_format($summary['delivered_orders_count']) }}</p>
                <p><strong>Total Orders Placed:</strong> {{ number_format($summary['orders_count']) }}</p>
            </div>
        </div>

        <!-- Collection Split Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900">Payment Inflows</h3>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">₹</span>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <div class="flex justify-between font-semibold text-slate-700 mb-1">
                        <span>Doorstep Cash (COD)</span>
                        <span>₹{{ number_format($summary['cash_collected'], 2) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $summary['total_collected'] > 0 ? min(100, round(($summary['cash_collected'] / $summary['total_collected']) * 100)) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between font-semibold text-slate-700 mb-1">
                        <span>Online UPI / Razorpay</span>
                        <span>₹{{ number_format($summary['online_collected'], 2) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ $summary['total_collected'] > 0 ? min(100, round(($summary['online_collected'] / $summary['total_collected']) * 100)) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Customer Dues Export:</span>
                <a href="{{ route('admin.reports.export-dues') }}" class="text-brand-600 font-bold hover:underline">Download CSV</a>
            </div>
        </div>

        <!-- Quick Reporting Actions -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-2">Dedicated Reports</h3>
                <p class="text-xs text-slate-500">Access deep-dive accounting, shrinkage, and driver performance audits.</p>
            </div>
            <div class="space-y-2 mt-4">
                <a href="{{ route('admin.reports.pnl') }}" class="block px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-800 transition-colors">
                    &rarr; Full Profit & Loss Statement (P&L)
                </a>
                <a href="{{ route('admin.reports.containers') }}" class="block px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-800 transition-colors">
                    &rarr; Container Fleet & Shrinkage Report
                </a>
                <a href="{{ route('admin.reports.fleet') }}" class="block px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-800 transition-colors">
                    &rarr; Driver Runsheet & Delivery Completion
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
