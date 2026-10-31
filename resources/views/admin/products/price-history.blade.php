@extends('layouts.admin')

@section('title', 'Price History: ' . $product->name . ' - Admin')
@section('page_title', 'Price Audit Trail')
@section('page_subtitle', $product->name . ' (' . $product->sku . ') Current Price: ₹' . number_format($product->selling_price, 2))

@section('header_actions')
<a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Product</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Historical Price Adjustment Ledger</h3>
            <span class="text-xs text-slate-500">{{ $histories->total() }} recorded changes</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Date & Time</th>
                        <th class="px-6 py-3.5">Previous Price</th>
                        <th class="px-6 py-3.5">New Price</th>
                        <th class="px-6 py-3.5">Variance</th>
                        <th class="px-6 py-3.5">Reason / Justification</th>
                        <th class="px-6 py-3.5">Modified By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $history)
                    @php
                        $diff = (float)$history->new_price - (float)$history->old_price;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-slate-700">
                            {{ $history->effective_from->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-500">
                            ₹{{ number_format($history->old_price, 2) }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">
                            ₹{{ number_format($history->new_price, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($diff > 0)
                                <span class="inline-flex items-center text-xs font-bold text-rose-600">
                                    +₹{{ number_format($diff, 2) }} &uarr;
                                </span>
                            @elseif($diff < 0)
                                <span class="inline-flex items-center text-xs font-bold text-emerald-600">
                                    -₹{{ number_format(abs($diff), 2) }} &darr;
                                </span>
                            @else
                                <span class="text-xs text-slate-400">Initial Setup</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-700">
                            {{ $history->reason ?: '—' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            {{ $history->changedBy?->name ?? 'System' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            No price history recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $histories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
