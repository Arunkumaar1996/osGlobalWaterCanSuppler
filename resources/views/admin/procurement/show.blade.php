@extends('layouts.admin')

@section('title', 'PO ' . $purchaseOrder->po_number . ' - Procurement')
@section('page_title', 'Purchase Order: ' . $purchaseOrder->po_number)
@section('page_subtitle', 'Issued to ' . $purchaseOrder->supplier?->company_name . ' on ' . $purchaseOrder->created_at->format('d M Y'))

@section('header_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.procurement.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Back to POs</span>
    </a>
    @if(in_array($purchaseOrder->status, ['issued', 'partially_received']))
    <a href="{{ route('admin.procurement.grn.create', $purchaseOrder) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Receive Stock (GRN)</span>
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- PO Header Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Status</span>
            <div class="mt-1">
                @if($purchaseOrder->status === 'completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Completed
                    </span>
                @elseif($purchaseOrder->status === 'partially_received')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Partially Received
                    </span>
                @elseif($purchaseOrder->status === 'issued')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Issued / In Transit
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        {{ ucfirst($purchaseOrder->status) }}
                    </span>
                @endif
            </div>
            <span class="text-[11px] text-slate-400 mt-2 block">Expected by {{ $purchaseOrder->expected_delivery_date?->format('d M Y') ?? '—' }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Order Value</span>
            <div class="text-2xl font-black text-slate-900">₹{{ number_format($purchaseOrder->total_amount, 2) }}</div>
            <span class="text-xs text-slate-500 mt-2 block">{{ $purchaseOrder->items->count() }} line items</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Supplier</span>
            <a href="{{ route('admin.suppliers.show', $purchaseOrder->supplier) }}" class="font-bold text-sm text-brand-600 hover:text-brand-700 truncate block">
                {{ $purchaseOrder->supplier?->company_name }}
            </a>
            <div class="text-xs text-slate-500">{{ $purchaseOrder->supplier?->phone }}</div>
            <div class="text-[11px] text-slate-400">{{ $purchaseOrder->supplier?->city }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Receiving Warehouse</span>
            <div class="font-bold text-sm text-slate-900">{{ $purchaseOrder->warehouse?->name }}</div>
            <div class="text-xs text-slate-500">{{ $purchaseOrder->branch?->name }}</div>
            <div class="text-[11px] text-slate-400">Created by: {{ $purchaseOrder->createdBy?->name ?? 'Admin' }}</div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Purchase Order Line Items</h3>
            <span class="text-xs text-slate-500">Stock audit of ordered vs delivered cans</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Product</th>
                        <th class="px-6 py-3.5 text-center">Ordered</th>
                        <th class="px-6 py-3.5 text-center">Received (Accepted)</th>
                        <th class="px-6 py-3.5 text-center">Damaged / Rejected</th>
                        <th class="px-6 py-3.5 text-center">Pending</th>
                        <th class="px-6 py-3.5 text-right">Unit Price</th>
                        <th class="px-6 py-3.5 text-right">Total Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchaseOrder->items as $item)
                    @php
                        $pending = max(0, $item->ordered_quantity - ($item->received_quantity + $item->rejected_quantity));
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900 block">{{ $item->product?->name }}</span>
                            <span class="text-xs text-slate-400">SKU: {{ $item->product?->sku }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-slate-900">
                            {{ $item->ordered_quantity }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-emerald-600">
                            {{ $item->received_quantity }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-rose-600">
                            {{ $item->rejected_quantity }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold {{ $pending > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                            {{ $pending }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-xs font-semibold text-slate-700">
                            ₹{{ number_format($item->unit_price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">
                            ₹{{ number_format($item->subtotal, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                    <tr>
                        <td colspan="6" class="px-6 py-3.5 text-right text-xs uppercase text-slate-600">Grand Total</td>
                        <td class="px-6 py-3.5 text-right font-black text-base text-slate-900">
                            ₹{{ number_format($purchaseOrder->total_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Goods Receipt Notes (GRN) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Goods Receipt Notes (GRN) Inspection History</h3>
                <p class="text-xs text-slate-500">Warehouse delivery inspections, accepted stock, and damaged can audit</p>
            </div>
            @if(in_array($purchaseOrder->status, ['issued', 'partially_received']))
            <a href="{{ route('admin.procurement.grn.create', $purchaseOrder) }}" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-100 transition-colors">
                + New GRN
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">GRN Number</th>
                        <th class="px-6 py-3.5">Receipt Date & Time</th>
                        <th class="px-6 py-3.5">Challan / DC No.</th>
                        <th class="px-6 py-3.5">Accepted Qty</th>
                        <th class="px-6 py-3.5">Damaged Qty</th>
                        <th class="px-6 py-3.5">Inspected By</th>
                        <th class="px-6 py-3.5">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchaseOrder->goodsReceipts as $grn)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-brand-600 text-xs">
                            {{ $grn->grn_number }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-700">
                            {{ $grn->received_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">
                            {{ $grn->supplier_challan_no ?? '—' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-emerald-600">
                            {{ $grn->items->sum('quantity_accepted') }} cans
                        </td>
                        <td class="px-6 py-4 font-bold text-rose-600">
                            {{ $grn->items->sum('quantity_damaged') }} cans
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            {{ $grn->receivedBy?->name ?? 'Staff' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $grn->remarks ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400 text-xs">
                            No goods receipts logged yet. When supplier delivers the stock, click "Receive Stock (GRN)".
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Supplier Invoices Generated -->
    @if($purchaseOrder->invoices->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-900">Generated Supplier Invoices (Accounts Payable)</h3>
            <p class="text-xs text-slate-500">Invoices automatically created for accepted received stock</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Invoice Number</th>
                        <th class="px-6 py-3.5">Invoice Date</th>
                        <th class="px-6 py-3.5">Due Date</th>
                        <th class="px-6 py-3.5">Billed Amount</th>
                        <th class="px-6 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @foreach($purchaseOrder->invoices as $inv)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-900 font-sans">
                            {{ $inv->invoice_number }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-sans">
                            {{ $inv->invoice_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-sans">
                            {{ $inv->due_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">
                            ₹{{ number_format($inv->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 font-sans">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                {{ ucfirst($inv->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
