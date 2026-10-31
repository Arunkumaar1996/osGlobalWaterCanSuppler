@extends('layouts.admin')

@section('title', 'Manifest ' . $manifest->manifest_number . ' - Dispatch')
@section('page_title', 'Dispatch Manifest: ' . $manifest->manifest_number)
@section('page_subtitle', 'Generated on ' . $manifest->generated_at->format('d M Y, h:i A') . ' for ' . ($manifest->driver_name ?? 'Delivery Fleet'))

@section('header_actions')
<div class="flex items-center gap-2 print:hidden">
    <a href="{{ route('admin.manifests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Back to Manifests</span>
    </a>
    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-sm hover:bg-slate-800 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Print Packing Slip</span>
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Printable Manifest Container -->
    <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm space-y-6 print:border-none print:shadow-none print:p-0">
        <!-- Manifest Header -->
        <div class="flex items-start justify-between border-b border-slate-200 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-black text-slate-900 tracking-tight">AquaFlow Water Distribution</span>
                    <span class="px-2 py-0.5 rounded bg-brand-50 text-brand-700 text-xs font-bold uppercase">Official Route Manifest</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $manifest->branch?->name }} | {{ $manifest->warehouse?->name ?? 'Direct Logistics' }}</p>
                <div class="font-mono text-sm font-bold text-brand-600 mt-2">Manifest No: {{ $manifest->manifest_number }}</div>
            </div>

            <div class="text-right text-xs space-y-1">
                <div><span class="text-slate-400">Date:</span> <span class="font-bold text-slate-800">{{ $manifest->generated_at->format('d M Y') }}</span></div>
                <div><span class="text-slate-400">Vehicle:</span> <span class="font-mono font-bold text-slate-900">{{ $manifest->vehicle_number ?? 'UNASSIGNED' }}</span></div>
                <div><span class="text-slate-400">Driver:</span> <span class="font-bold text-slate-900">{{ $manifest->driver_name ?? 'UNASSIGNED' }} ({{ $manifest->driver_phone ?? 'N/A' }})</span></div>
            </div>
        </div>

        <!-- Stop by Stop Packing List -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200">
                <thead class="bg-slate-100 text-[10px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-3 border-r border-slate-200 w-10 text-center">Stop</th>
                        <th class="p-3 border-r border-slate-200">Order & Customer</th>
                        <th class="p-3 border-r border-slate-200">Delivery Address & Landmark</th>
                        <th class="p-3 border-r border-slate-200">Time Slot</th>
                        <th class="p-3 border-r border-slate-200">Cans to Deliver</th>
                        <th class="p-3 border-r border-slate-200 text-center">Empty Cans Due</th>
                        <th class="p-3 border-r border-slate-200 text-right">Collect (₹)</th>
                        <th class="p-3 w-32 text-center">Customer Sig / OTP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php $totalDeliverCans = 0; $totalCollectCash = 0; @endphp
                    @foreach($manifest->fulfillments as $idx => $ful)
                    @php
                        $ord = $ful->order;
                        $fulCans = $ful->total_quantity;
                        $totalDeliverCans += $fulCans;
                        $cashToCollect = ($ord->payment_method_preferred === 'cod' && $ord->payment_status === 'unpaid') ? $ord->total_amount : 0.00;
                        $totalCollectCash += $cashToCollect;
                    @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="p-3 border-r border-slate-200 text-center font-bold text-sm text-slate-900">
                            {{ $loop->iteration }}
                        </td>
                        <td class="p-3 border-r border-slate-200">
                            <span class="font-mono font-bold text-slate-900 block">{{ $ord->order_number }}</span>
                            <span class="font-bold text-slate-800">{{ $ord->customer?->user?->name }}</span>
                            <span class="font-mono text-slate-500 block text-[11px]">{{ $ord->customer?->user?->mobile }}</span>
                        </td>
                        <td class="p-3 border-r border-slate-200 leading-tight">
                            <div>{{ $ord->snapshot_address['address_line1'] ?? '' }}, {{ $ord->snapshot_address['area'] ?? '' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $ord->snapshot_address['pincode'] ?? '' }}</div>
                            @if(!empty($ord->snapshot_address['landmark']))
                                <div class="text-[10px] text-slate-500 font-semibold mt-0.5">LM: {{ $ord->snapshot_address['landmark'] }}</div>
                            @endif
                        </td>
                        <td class="p-3 border-r border-slate-200 font-semibold text-slate-800">
                            {{ $ord->deliverySlot?->name }}
                        </td>
                        <td class="p-3 border-r border-slate-200 font-bold text-slate-900">
                            @foreach($ful->items as $fItem)
                                <div>{{ $fItem->quantity }}x {{ $fItem->product?->name }}</div>
                            @endforeach
                        </td>
                        <td class="p-3 border-r border-slate-200 text-center font-mono font-bold text-amber-700">
                            {{ $fulCans }} cans
                        </td>
                        <td class="p-3 border-r border-slate-200 text-right font-mono font-bold">
                            @if($cashToCollect > 0)
                                <span class="text-rose-600">₹{{ number_format($cashToCollect, 2) }}</span>
                                <span class="text-[9px] text-rose-500 block font-sans uppercase">COD Cash</span>
                            @else
                                <span class="text-emerald-600">PAID / CREDIT</span>
                            @endif
                        </td>
                        <td class="p-3 border-slate-200 text-center border-b border-dashed">
                            <div class="h-8 border-b border-slate-300"></div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="p-3 text-right uppercase text-[11px]">Total Route Summary:</td>
                        <td class="p-3 font-black text-slate-900">{{ $totalDeliverCans }} cans</td>
                        <td class="p-3 text-center font-black text-amber-800">{{ $totalDeliverCans }} empty</td>
                        <td class="p-3 text-right font-black text-slate-900 font-mono">₹{{ number_format($totalCollectCash, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Manifest Footer & Signatures -->
        <div class="pt-8 grid grid-cols-3 gap-8 text-center text-xs text-slate-500 border-t border-slate-200">
            <div>
                <div class="h-12 border-b border-slate-300 mb-2"></div>
                <span>Warehouse Supervisor Sign</span>
            </div>
            <div>
                <div class="h-12 border-b border-slate-300 mb-2"></div>
                <span>Driver Departure Sign</span>
            </div>
            <div>
                <div class="h-12 border-b border-slate-300 mb-2"></div>
                <span>Gate Out Security Check</span>
            </div>
        </div>
    </div>
</div>
@endsection
