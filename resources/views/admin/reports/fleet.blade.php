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
                <span class="text-slate-800 font-semibold">Fleet Efficiency</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Delivery Fleet & Driver Efficiency</h1>
            <p class="text-sm text-slate-500 mt-1">Driver runsheet fulfillment rate, on-time doorstep handovers, and cash collections.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                <span>Manage Fleet & Slots &rarr;</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                Back to Analytics
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.fleet') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors">
                    Filter Fleet
                </button>
            </div>
        </form>
    </div>

    <!-- Aggregate Efficiency Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Runsheet Trips -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Runsheets</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($efficiency['total_trips']) }} trips</p>
            <p class="text-xs text-slate-400 mt-1">{{ number_format($efficiency['total_stops']) }} delivery stops</p>
        </div>

        <!-- Completion Rate -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Fulfillment Rate</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $efficiency['completion_rate'] }}%</p>
            <p class="text-xs text-slate-400 mt-1">{{ number_format($efficiency['delivered_stops']) }} successfully delivered</p>
        </div>

        <!-- Cash Reconciled -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">COD Collected</p>
            <p class="text-2xl font-black text-slate-900 mt-1">₹{{ number_format($efficiency['total_cash_collected'], 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Doorstep cash receipts</p>
        </div>

        <!-- Empties Collected -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Empties Recovered</p>
            <p class="text-2xl font-black text-brand-600 mt-1">{{ number_format($efficiency['total_empties_returned']) }} cans</p>
            <p class="text-xs text-slate-400 mt-1">Containers brought back</p>
        </div>
    </div>

    <!-- Driver / Partner Breakdown Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Driver & Partner Efficiency Scorecard</h2>
            <span class="text-xs text-slate-500">{{ $efficiency['drivers']->count() }} executives</span>
        </div>

        @if($efficiency['drivers']->isEmpty())
            <div class="p-12 text-center text-slate-500 text-xs">
                No delivery runsheets recorded in the selected date window.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Executive / Fleet Partner</th>
                            <th class="px-6 py-3.5 text-center">Trips Completed</th>
                            <th class="px-6 py-3.5 text-center">Total Stops</th>
                            <th class="px-6 py-3.5 text-center">Delivered</th>
                            <th class="px-6 py-3.5 text-center">Failed</th>
                            <th class="px-6 py-3.5 text-center">Success Rate</th>
                            <th class="px-6 py-3.5 text-right">Cash Handover</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($efficiency['drivers'] as $driver)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-slate-900">
                                    {{ $driver['name'] }}
                                </td>
                                <td class="px-6 py-3.5 text-center text-slate-700">{{ $driver['trips_count'] }}</td>
                                <td class="px-6 py-3.5 text-center font-medium">{{ $driver['total_stops'] }}</td>
                                <td class="px-6 py-3.5 text-center font-bold text-emerald-600">{{ $driver['delivered_stops'] }}</td>
                                <td class="px-6 py-3.5 text-center font-bold text-rose-500">{{ $driver['failed_stops'] }}</td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $driver['success_rate'] >= 90 ? 'bg-emerald-100 text-emerald-800' : ($driver['success_rate'] >= 70 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $driver['success_rate'] }}%
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-slate-900">
                                    ₹{{ number_format($driver['cash_collected'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
