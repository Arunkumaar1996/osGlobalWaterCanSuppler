@extends('layouts.admin')

@section('title', 'Procurement & Purchase Orders - Admin')
@section('page_title', 'Procurement & Purchase Orders')
@section('page_subtitle', 'Manage water can sourcing, stock orders to suppliers, and goods receipts')

@section('header_actions')
<a href="{{ route('admin.procurement.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white font-semibold text-sm hover:bg-brand-700 shadow-sm transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span>Create Purchase Order</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.procurement.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Supplier</label>
                <select name="supplier_id" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Warehouse</label>
                <select name="warehouse_id" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued / Pending Delivery</option>
                    <option value="partially_received" {{ request('status') === 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.procurement.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- POs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">PO Number</th>
                        <th class="px-6 py-3.5">Supplier</th>
                        <th class="px-6 py-3.5">Destination Warehouse</th>
                        <th class="px-6 py-3.5">Expected Delivery</th>
                        <th class="px-6 py-3.5">Total Amount</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.procurement.show', $po) }}" class="font-mono font-bold text-slate-900 hover:text-brand-600 block">
                                {{ $po->po_number }}
                            </a>
                            <span class="text-xs text-slate-400">Created {{ $po->created_at->format('d M Y') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-slate-800 block">{{ $po->supplier?->company_name }}</span>
                            <span class="text-xs text-slate-400">{{ $po->supplier?->contact_person }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            {{ $po->warehouse?->name ?? 'Default Warehouse' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            {{ $po->expected_delivery_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">
                            ₹{{ number_format($po->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($po->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Completed
                                </span>
                            @elseif($po->status === 'partially_received')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Partially Received
                                </span>
                            @elseif($po->status === 'issued')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Issued
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                    {{ ucfirst($po->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(in_array($po->status, ['issued', 'partially_received']))
                                <a href="{{ route('admin.procurement.grn.create', $po) }}" class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg transition-colors">
                                    Receive Stock (GRN)
                                </a>
                                @endif
                                <a href="{{ route('admin.procurement.show', $po) }}" class="p-1.5 text-slate-400 hover:text-brand-600 rounded-lg hover:bg-slate-100 transition-colors" title="View PO Details">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            No purchase orders found matching the filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseOrders->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $purchaseOrders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
