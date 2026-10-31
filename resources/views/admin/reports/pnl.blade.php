@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('admin.reports.index') }}" class="hover:text-brand-600">Analytics</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">P&L Statement</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Profit & Loss (P&L) Statement</h1>
            <p class="text-sm text-slate-500 mt-1">Consolidated trading, operating income, COGS, overheads, and net profit margins.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Statement
            </button>
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                &larr; Back to Analytics
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm print:hidden">
        <form method="GET" action="{{ route('admin.reports.pnl') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Branch</label>
                <select name="branch_id" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors">
                    Filter Period
                </button>
            </div>
        </form>
    </div>

    <!-- Printable Statement Sheet -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6 max-w-4xl mx-auto">
        <!-- Statement Header -->
        <div class="text-center pb-6 border-b border-slate-200">
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Statement of Profit & Loss</h2>
            <p class="text-xs font-semibold text-slate-500 mt-1">
                For the period: <strong class="text-slate-800">{{ date('d F, Y', strtotime($pnl['period']['start'])) }}</strong> to <strong class="text-slate-800">{{ date('d F, Y', strtotime($pnl['period']['end'])) }}</strong>
            </p>
            <p class="text-[11px] text-slate-400 mt-0.5">Amounts stated in Indian National Rupee (INR ₹)</p>
        </div>

        <!-- Section 1: Operating Income -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100 flex items-center justify-between">
                <span>1. Operating Revenue & Inflows</span>
                <span class="text-emerald-600 font-bold">Total Inflows</span>
            </h3>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50 text-slate-700">
                    <span class="pl-4">Water Can Sales (Net of Discounts)</span>
                    <span class="font-medium">₹{{ number_format($pnl['income']['water_sales'], 2) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50 text-slate-700">
                    <span class="pl-4">Delivery & Conveyance Fees</span>
                    <span class="font-medium">₹{{ number_format($pnl['income']['delivery_charges'], 2) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50 text-slate-700">
                    <span class="pl-4">Missing / Damaged Can Penalty Recoveries</span>
                    <span class="font-medium">₹{{ number_format($pnl['income']['lost_can_penalties'], 2) }}</span>
                </div>
                <div class="flex justify-between py-2 font-bold text-slate-900 bg-emerald-50/60 px-4 rounded-xl text-xs">
                    <span>Total Operating Income (A)</span>
                    <span class="text-emerald-700">₹{{ number_format($pnl['income']['total_income'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Cost of Goods Sold (COGS) & Gross Profit -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100 flex items-center justify-between">
                <span>2. Cost of Goods Sold (COGS)</span>
                <span class="text-slate-500 font-medium">Direct Sourcing</span>
            </h3>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50 text-slate-700">
                    <span class="pl-4">Supplier Water Procurement (Goods Receipts GRN)</span>
                    <span class="font-medium">₹{{ number_format($pnl['cogs']['procurement_cogs'], 2) }}</span>
                </div>
                <div class="flex justify-between py-2 font-bold text-slate-900 bg-slate-100 px-4 rounded-xl text-xs">
                    <span>Gross Trading Profit (A - COGS)</span>
                    <span class="text-slate-900">₹{{ number_format($pnl['cogs']['gross_profit'], 2) }} ({{ $pnl['cogs']['gross_margin_percent'] }}%)</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Operating Expenses (Overheads) -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100 flex items-center justify-between">
                <span>3. Operating & Administrative Expenses</span>
                <span class="text-rose-600 font-bold">Overheads</span>
            </h3>

            <div class="space-y-2 text-xs">
                @if($pnl['expenses']['by_category']->isEmpty())
                    <p class="text-slate-400 pl-4 py-1 italic">No operating expenses logged for this period.</p>
                @else
                    @foreach($pnl['expenses']['by_category'] as $exp)
                        <div class="flex justify-between py-1 border-b border-slate-50 text-slate-700">
                            <span class="pl-4">{{ $exp['category_name'] }}</span>
                            <span class="font-medium text-rose-600">₹{{ number_format($exp['amount'], 2) }}</span>
                        </div>
                    @endforeach
                @endif
                <div class="flex justify-between py-2 font-bold text-slate-900 bg-rose-50/60 px-4 rounded-xl text-xs">
                    <span>Total Operating Expenses (B)</span>
                    <span class="text-rose-700">₹{{ number_format($pnl['expenses']['total_expenses'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Section 4: Net Operating Profit -->
        <div class="pt-4 border-t-2 border-slate-900">
            <div class="flex justify-between items-center py-4 px-6 rounded-2xl {{ $pnl['net_profit'] >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} shadow-md">
                <div>
                    <h4 class="text-sm font-black uppercase tracking-wider">Net Operating Profit / (Loss)</h4>
                    <p class="text-xs opacity-90 mt-0.5">Net Profit Margin: <strong>{{ $pnl['net_margin_percent'] }}%</strong></p>
                </div>
                <div class="text-2xl font-black">
                    ₹{{ number_format($pnl['net_profit'], 2) }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
