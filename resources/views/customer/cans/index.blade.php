@extends('layouts.customer')

@section('title', 'Empty-Can Exchange Balance')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                <span>&rsaquo;</span>
                <span class="font-bold text-slate-800">Container Balance</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Empty-Can Exchange Balance</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}#products" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Order Water Now
            </a>
            <a href="{{ route('customer.ledger.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                Financial Passbook
            </a>
        </div>
    </div>

    <!-- Container Holdings Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Held Containers -->
        <div class="bg-gradient-to-br from-cyan-600 to-blue-700 text-white p-5 rounded-3xl shadow-lg flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-cyan-100">Total Containers Held</span>
                <p class="text-3xl font-black mt-1">{{ $totalHeld }} <span class="text-xs font-sans font-semibold text-cyan-200">Cans</span></p>
            </div>
            <div class="mt-4 pt-3 border-t border-cyan-500/40 text-[11px] text-cyan-100 flex items-center justify-between">
                <span>1:1 Exchange Model</span>
                <span class="font-bold">Active</span>
            </div>
        </div>

        <!-- Breakdown Per Can Type -->
        @foreach($balances as $bal)
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $bal->can_type->name }}</span>
                    <p class="text-2xl font-black text-slate-900 mt-1">
                        {{ $bal->held_cans_count }} <span class="text-xs font-sans font-semibold text-slate-500">Held</span>
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                    <span>Capacity: {{ $bal->can_type->capacity_litres }}L</span>
                    <span class="font-mono text-slate-400">₹{{ number_format($bal->can_type->deposit_amount, 0) }} value</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Exchange Policy Banner -->
    <div class="bg-cyan-50/70 border border-cyan-200 rounded-3xl p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4 text-xs">
        <div class="w-10 h-10 rounded-2xl bg-cyan-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <div class="space-y-0.5">
            <h4 class="font-bold text-cyan-950 text-sm">How the 1:1 Can Exchange Works</h4>
            <p class="text-cyan-800 leading-relaxed">
                When our delivery personnel arrives at your doorstep, hand over 1 empty can for each full can delivered.
                If you do not have an empty can to exchange, your container balance will increase. Container balances are kept strictly independent from money/financial dues.
            </p>
        </div>
    </div>

    <!-- Container Physical Movement Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Container Movement Passbook</h3>
                <p class="text-xs text-slate-500">Complete physical audit history of cans delivered, empties returned, and container balance changes</p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
        </div>

        @if($movements->isEmpty())
            <div class="py-12 text-center text-slate-500 text-xs">
                <div class="w-12 h-12 rounded-full bg-cyan-50 text-cyan-600 flex items-center justify-center mx-auto mb-2 font-bold text-lg">
                    &bull;
                </div>
                <p class="font-bold text-slate-800 text-sm">No Container Movements Recorded Yet</p>
                <p class="text-slate-500 mt-1">When full cans are delivered and empty bottles exchanged, transactions are logged here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-3">Date & Time</th>
                            <th class="py-3 px-3">Can Type</th>
                            <th class="py-3 px-3">Movement Type</th>
                            <th class="py-3 px-3">Remarks / Order</th>
                            <th class="py-3 px-3 text-right">Delivered Full (+)</th>
                            <th class="py-3 px-3 text-right">Collected Empty (-)</th>
                            <th class="py-3 px-3 text-right">Held Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($movements as $m)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 text-slate-600 whitespace-nowrap">
                                    {{ $m->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $m->canType?->name ?? 'Standard Can' }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($m->movement_type === 'delivery_exchange')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-50 text-brand-700 border border-brand-200">
                                            Delivery Exchange
                                        </span>
                                    @elseif($m->movement_type === 'return_collection')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Empty Return
                                        </span>
                                    @elseif($m->movement_type === 'missing_penalty')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                            Missing Penalty
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                            Audit Reconciliation
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-700">
                                    <div>{{ $m->notes }}</div>
                                    @if($m->reference_type === \App\Models\Order::class && $m->reference_id)
                                        <a href="{{ route('customer.orders.show', $m->reference_id) }}" class="text-[11px] font-bold text-brand-600 hover:underline">
                                            View Order &rarr;
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold {{ $m->cans_delivered_full > 0 ? 'text-brand-600' : 'text-slate-300' }}">
                                    {{ $m->cans_delivered_full > 0 ? '+' . $m->cans_delivered_full : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold {{ $m->cans_collected_empty > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                                    {{ $m->cans_collected_empty > 0 ? '-' . $m->cans_collected_empty : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black text-slate-900 text-sm">
                                    {{ $m->balance_after_movement }} cans
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
