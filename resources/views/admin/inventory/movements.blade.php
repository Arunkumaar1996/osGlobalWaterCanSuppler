@extends('layouts.admin')

@section('title', 'Inventory Movement Ledger - Admin')
@section('page_title', 'Inventory Movement Audit Ledger')
@section('page_subtitle', 'Immutable audit trail of all physical container migrations, refilling cycles, and quality write-offs')

@section('header_actions')
<a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Inventory</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.inventory.movements') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Warehouse</label>
                <select name="warehouse_id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Product SKU</label>
                <select name="product_id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Products</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                            {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Movement Type</label>
                <select name="movement_type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Types</option>
                    <option value="purchase_received" {{ request('movement_type') === 'purchase_received' ? 'selected' : '' }}>Purchase Received</option>
                    <option value="sent_to_cleaning" {{ request('movement_type') === 'sent_to_cleaning' ? 'selected' : '' }}>Sent to Cleaning</option>
                    <option value="refilled_to_full" {{ request('movement_type') === 'refilled_to_full' ? 'selected' : '' }}>Refilled to Full</option>
                    <option value="damaged_writeoff" {{ request('movement_type') === 'damaged_writeoff' ? 'selected' : '' }}>Damaged Write-off</option>
                    <option value="transfer_out" {{ request('movement_type') === 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                    <option value="transfer_in" {{ request('movement_type') === 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                    <option value="stock_audit_adjustment" {{ request('movement_type') === 'stock_audit_adjustment' ? 'selected' : '' }}>Stock Audit Adjustment</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.inventory.movements') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Movements Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Timestamp</th>
                        <th class="px-5 py-3.5">Warehouse & SKU</th>
                        <th class="px-5 py-3.5">Movement Type</th>
                        <th class="px-5 py-3.5 text-center">State Transition</th>
                        <th class="px-5 py-3.5 text-center">Quantity</th>
                        <th class="px-5 py-3.5 text-center">Balances After (F / E / C / D)</th>
                        <th class="px-5 py-3.5">Notes & Performed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-3.5 text-slate-600 font-sans">
                            <span class="block font-semibold text-slate-900">{{ $m->created_at->format('d M Y') }}</span>
                            <span class="text-[11px] text-slate-400">{{ $m->created_at->format('h:i:s A') }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-sans">
                            <span class="font-bold text-slate-900 block">{{ $m->product?->name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $m->warehouse?->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-sans">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                {{ $m->movement_type === 'purchase_received' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                {{ $m->movement_type === 'sent_to_cleaning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                {{ $m->movement_type === 'refilled_to_full' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                {{ $m->movement_type === 'damaged_writeoff' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                {{ in_array($m->movement_type, ['transfer_in', 'transfer_out']) ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                {{ $m->movement_type === 'stock_audit_adjustment' ? 'bg-slate-100 text-slate-700' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $m->movement_type)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center font-bold text-slate-700">
                            {{ ucfirst($m->state_from) }} &rarr; {{ ucfirst($m->state_to) }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-black text-sm text-slate-900">
                            {{ $m->quantity }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-slate-600">
                            <span class="text-emerald-700 font-bold">{{ $m->full_stock_after }}F</span> /
                            <span class="text-amber-700 font-bold">{{ $m->empty_stock_after }}E</span> /
                            <span class="text-blue-700 font-bold">{{ $m->under_cleaning_after }}C</span> /
                            <span class="text-rose-700 font-bold">{{ $m->damaged_stock_after }}D</span>
                        </td>
                        <td class="px-5 py-3.5 font-sans text-xs">
                            <div class="text-slate-800">{{ $m->notes ?: '—' }}</div>
                            <div class="text-[11px] text-slate-400">By: {{ $m->user?->name ?? 'System' }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-sans">
                            No inventory movements recorded matching the filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
