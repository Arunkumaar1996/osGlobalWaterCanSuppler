@extends('layouts.admin')

@section('title', 'Record GRN: ' . $purchaseOrder->po_number . ' - Procurement')
@section('page_title', 'Goods Receipt Note (GRN)')
@section('page_subtitle', 'Inspect physical delivery for PO ' . $purchaseOrder->po_number . ' from ' . $purchaseOrder->supplier?->company_name)

@section('header_actions')
<a href="{{ route('admin.procurement.show', $purchaseOrder) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to PO</span>
</a>
@endsection

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('admin.procurement.grn.store', $purchaseOrder) }}" class="space-y-6">
        @csrf

        <!-- Delivery Header Info -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Delivery Information & Challan</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Supplier Challan / DC No.</label>
                    <input type="text" name="supplier_challan_no" value="{{ old('supplier_challan_no') }}" placeholder="e.g. DC-2026-9041" class="w-full px-4 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Vendor's printed delivery challan</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Inspection Remarks / Seal Condition</label>
                    <input type="text" name="remarks" value="{{ old('remarks') }}" placeholder="e.g. All batch seal stickers checked; truck vehicle TN-04-AB-1234" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Log any seal defects or vehicle notes</p>
                </div>
            </div>
        </div>

        <!-- Inspection Items Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Stock Inspection & Can Quality Check</h3>
                <p class="text-xs text-slate-500">Enter accepted good stock and record any cracked, leaking, or rejected cans</p>
            </div>

            @error('items')
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-semibold">
                    {{ $message }}
                </div>
            @enderror

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3 text-center">Ordered</th>
                            <th class="px-4 py-3 text-center">Prior Received</th>
                            <th class="px-4 py-3 text-center">Pending Qty</th>
                            <th class="px-4 py-3 text-center w-36">Accepted (Good)</th>
                            <th class="px-4 py-3 text-center w-36">Damaged / Rejected</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($purchaseOrder->items as $index => $item)
                        @php
                            $pending = max(0, $item->ordered_quantity - ($item->received_quantity + $item->rejected_quantity));
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-4">
                                <span class="font-bold text-slate-900 block">{{ $item->product?->name }}</span>
                                <span class="text-xs text-slate-400">SKU: {{ $item->product?->sku }}</span>
                                <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-slate-700">
                                {{ $item->ordered_quantity }}
                            </td>
                            <td class="px-4 py-4 text-center text-xs font-semibold text-slate-600">
                                {{ $item->received_quantity }} (+{{ $item->rejected_quantity }} rejected)
                            </td>
                            <td class="px-4 py-4 text-center font-bold {{ $pending > 0 ? 'text-brand-600' : 'text-slate-400' }}">
                                {{ $pending }}
                            </td>
                            <td class="px-4 py-4">
                                <input type="number" name="items[{{ $index }}][quantity_accepted]" value="{{ old("items.{$index}.quantity_accepted", $pending) }}" min="0" max="{{ $pending }}" class="w-full px-3 py-2 text-center text-sm font-bold text-emerald-700 bg-emerald-50/40 border border-emerald-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-4 py-4">
                                <input type="number" name="items[{{ $index }}][quantity_damaged]" value="{{ old("items.{$index}.quantity_damaged", 0) }}" min="0" max="{{ $pending }}" class="w-full px-3 py-2 text-center text-sm font-bold text-rose-700 bg-rose-50/40 border border-rose-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1 mt-4">
                <span class="font-bold block">Important Business Logic Note:</span>
                <p>1. Only <span class="font-bold text-emerald-800">Accepted Cans</span> will be billed to the supplier and added to warehouse inventory.</p>
                <p>2. Damaged cans will be recorded as rejected and excluded from accounts payable invoices.</p>
                <p>3. A Supplier Invoice and double-entry ledger credit liability will be posted automatically upon submission.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.procurement.show', $purchaseOrder) }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white font-bold text-sm rounded-xl hover:bg-emerald-700 shadow-sm transition-colors">
                Confirm & Receive Stock
            </button>
        </div>
    </form>
</div>
@endsection
