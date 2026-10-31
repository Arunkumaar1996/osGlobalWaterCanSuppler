@extends('layouts.customer')

@section('title', 'Financial Ledger Passbook')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                <span>&rsaquo;</span>
                <span class="font-bold text-slate-800">Financial Ledger</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Financial Passbook</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('customer.dues.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Pay Outstanding Dues
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
        </div>
    </div>

    <!-- Passbook Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Outstanding Due</span>
            <p class="text-2xl font-black text-slate-900 mt-1">₹{{ number_format($profile->outstanding_financial_balance, 2) }}</p>
            <span class="text-[11px] font-semibold text-slate-500 mt-1 block">Live real-time balance</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Billed Charges</span>
            <p class="text-2xl font-black text-rose-600 mt-1">₹{{ number_format($profile->ledger()->sum('debit_amount'), 2) }}</p>
            <span class="text-[11px] font-semibold text-slate-500 mt-1 block">Cumulative debits posted</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Payments Cleared</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($profile->ledger()->sum('credit_amount'), 2) }}</p>
            <span class="text-[11px] font-semibold text-slate-500 mt-1 block">Cumulative credits recorded</span>
        </div>
    </div>

    <!-- Double-Entry Transaction Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Transaction History</h3>
                <p class="text-xs text-slate-500">Double-entry audit log of order charges, receipts, and account adjustments</p>
            </div>
            <div class="text-[11px] text-slate-400 italic">
                * Strict segregation: Container bottle exchanges are logged separately in Can Balances.
            </div>
        </div>

        @if($entries->isEmpty())
            <div class="py-12 text-center text-slate-500 text-xs">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 font-bold text-lg">
                    &bull;
                </div>
                <p class="font-bold text-slate-800 text-sm">No Ledger Transactions Yet</p>
                <p class="text-slate-500 mt-1">When you place orders on credit or make payments, each entry is recorded here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-3">Date & Time</th>
                            <th class="py-3 px-3">Type</th>
                            <th class="py-3 px-3">Reference / Remarks</th>
                            <th class="py-3 px-3 text-right">Debit (+)</th>
                            <th class="py-3 px-3 text-right">Credit (-)</th>
                            <th class="py-3 px-3 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-slate-800">
                        @foreach($entries as $entry)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 font-sans text-slate-600 whitespace-nowrap">
                                    {{ $entry->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 px-3 font-sans whitespace-nowrap">
                                    @if($entry->transaction_type === 'order_charge')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                                            Order Charge
                                        </span>
                                    @elseif($entry->transaction_type === 'payment_received')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Payment Received
                                        </span>
                                    @elseif($entry->transaction_type === 'credit_adjustment')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-sky-50 text-sky-800 border border-sky-200">
                                            Credit Adjustment
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-50 text-purple-800 border border-purple-200">
                                            Debit Adjustment
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-sans text-slate-700">
                                    <div class="font-medium text-slate-900">{{ $entry->notes }}</div>
                                    @if($entry->reference_type === \App\Models\Order::class && $entry->reference_id)
                                        <a href="{{ route('customer.orders.show', $entry->reference_id) }}" class="text-[11px] font-bold text-brand-600 hover:underline">
                                            View Order Details &rarr;
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-bold {{ $entry->debit_amount > 0 ? 'text-rose-600' : 'text-slate-300' }}">
                                    {{ $entry->debit_amount > 0 ? '₹' . number_format($entry->debit_amount, 2) : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold {{ $entry->credit_amount > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                                    {{ $entry->credit_amount > 0 ? '₹' . number_format($entry->credit_amount, 2) : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-black text-slate-900">
                                    ₹{{ number_format($entry->running_balance, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
