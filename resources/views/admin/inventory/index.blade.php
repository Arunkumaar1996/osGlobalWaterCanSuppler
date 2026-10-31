@extends('layouts.admin')

@section('title', 'Warehouse Inventory & Containers - Admin')
@section('page_title', 'Warehouse Inventory & Container Cycles')
@section('page_subtitle', 'Multi-state physical stock tracking: Full stock, empty reusable cans, cleaning bay, and damaged stock')

@section('header_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.inventory.transfers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        <span>Transfers</span>
    </a>
    <a href="{{ route('admin.inventory.movements') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-sm hover:bg-slate-800 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        <span>Audit Ledger</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    cycleModal: false,
    adjustModal: false,
    damageModal: false,
    selectedStock: null,
    openCycle(stock) { this.selectedStock = stock; this.cycleModal = true; },
    openAdjust(stock) { this.selectedStock = stock; this.adjustModal = true; },
    openDamage(stock) { this.selectedStock = stock; this.damageModal = true; }
}">

    <!-- Warehouse Selector & Top KPI Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider shrink-0">Warehouse:</label>
            <select name="warehouse_id" onchange="this.form.submit()" class="px-3 py-1.5 text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $selectedWarehouse?->id === $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})
                    </option>
                @endforeach
            </select>
        </form>

        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Real-time container cycle & ledger synchronization</span>
        </div>
    </div>

    <!-- Stock KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Full Stock Ready -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Full Stock Ready</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['total_full']) }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold block mt-1">Available for delivery</span>
        </div>

        <!-- Empty Stock -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Empty Returns</span>
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['total_empty']) }}</div>
            <span class="text-[10px] text-amber-600 font-semibold block mt-1">Awaiting washing bay</span>
        </div>

        <!-- Cleaning Bay -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Under Washing / Refill</span>
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['total_cleaning']) }}</div>
            <span class="text-[10px] text-blue-600 font-semibold block mt-1">Washing & UV Sanitizing</span>
        </div>

        <!-- Damaged Stock -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Damaged / Condemned</span>
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            </div>
            <div class="text-2xl font-black text-rose-600 mt-2">{{ number_format($summary['total_damaged']) }}</div>
            <span class="text-[10px] text-rose-500 font-semibold block mt-1">Quarantined defective</span>
        </div>

        <!-- Alerts -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm col-span-2 md:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stock Alerts</span>
                <span class="w-2 h-2 rounded-full {{ $summary['low_stock_count'] > 0 ? 'bg-rose-500 animate-ping' : 'bg-slate-300' }}"></span>
            </div>
            <div class="text-2xl font-black {{ $summary['low_stock_count'] > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-2">
                {{ $summary['low_stock_count'] }}
            </div>
            <span class="text-[10px] {{ $summary['low_stock_count'] > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }} block mt-1">
                {{ $summary['low_stock_count'] > 0 ? 'SKUs Below Reorder Level' : 'Healthy inventory levels' }}
            </span>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Physical Stock Balances by Product SKU</h3>
            <span class="text-xs text-slate-500">{{ $stocks->count() }} managed SKUs in {{ $selectedWarehouse?->name }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Product SKU</th>
                        <th class="px-6 py-3.5 text-center">Full Stock (Avail / Res)</th>
                        <th class="px-6 py-3.5 text-center">Empty Cans</th>
                        <th class="px-6 py-3.5 text-center">Cleaning Bay</th>
                        <th class="px-6 py-3.5 text-center">Damaged</th>
                        <th class="px-6 py-3.5 text-center">Total Bottles</th>
                        <th class="px-6 py-3.5">Health Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @forelse($stocks as $stock)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-sans">
                            <span class="font-bold text-slate-900 block">{{ $stock->product?->name }}</span>
                            <span class="text-xs text-slate-400 font-mono">{{ $stock->product?->sku }} ({{ $stock->product?->capacity_value }} {{ $stock->product?->capacity_unit }})</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="font-black text-sm text-slate-900">{{ $stock->full_stock }}</div>
                            <div class="text-[10px] text-slate-400 font-sans">
                                <span class="text-emerald-700 font-bold">{{ $stock->available_stock }} avail</span> / 
                                <span class="text-amber-700">{{ $stock->reserved_stock }} res</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-amber-700">
                            {{ $stock->empty_stock }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-blue-700">
                            {{ $stock->under_cleaning }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-rose-600">
                            {{ $stock->damaged_stock }}
                        </td>
                        <td class="px-6 py-4 text-center font-black text-slate-900">
                            {{ $stock->total_physical_cans }}
                        </td>
                        <td class="px-6 py-4 font-sans">
                            @if($stock->is_low_stock)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Low Stock (&le; {{ $stock->reorder_level }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Adequate
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-sans">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($stock->product?->requires_can_exchange)
                                <button type="button" @click="openCycle({{ json_encode($stock) }})" title="Cycle Cans (Wash / Refill)" class="px-2.5 py-1 text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-600 hover:text-white rounded-lg transition-colors">
                                    Cycle Cans
                                </button>
                                @endif
                                <button type="button" @click="openDamage({{ json_encode($stock) }})" title="Report Damaged / Leaking Can" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                                <button type="button" @click="openAdjust({{ json_encode($stock) }})" title="Reconcile Physical Audit" class="p-1.5 text-slate-400 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 font-sans">
                            No stock records found for this warehouse.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Container Refill / Wash Cycle -->
    <div x-show="cycleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="cycleModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4" @click.away="cycleModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Container Wash & Refill Lifecycle</h3>
                <button type="button" @click="cycleModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <template x-if="selectedStock">
                <form method="POST" :action="'/admin/inventory/' + selectedStock.id + '/cycle-cleaning'" class="space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Product</span>
                        <span class="text-sm font-bold text-slate-900" x-text="selectedStock.product.name"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl text-xs">
                        <div>
                            <span class="text-slate-400 block">Empty Returns Available</span>
                            <span class="font-bold text-amber-700" x-text="selectedStock.empty_stock + ' cans'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">In Washing Bay</span>
                            <span class="font-bold text-blue-700" x-text="selectedStock.under_cleaning + ' cans'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Action <span class="text-rose-500">*</span></label>
                        <select name="action" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="to_cleaning">1. Move Empty Returns &rarr; Washing Bay</option>
                            <option value="to_full">2. Complete Wash & Refill &rarr; Full Stock Ready</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Quantity to Move <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" min="1" required class="w-full px-3 py-2 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batch / Quality QC Notes</label>
                        <input type="text" name="notes" placeholder="e.g. Batch B-204, UV pass at 4 bar pressure" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" @click="cycleModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl hover:bg-brand-700">
                            Confirm Cycle Movement
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- Modal 2: Report Damaged / Condemned Can -->
    <div x-show="damageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="damageModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4" @click.away="damageModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Quarantine Damaged Container</h3>
                <button type="button" @click="damageModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <template x-if="selectedStock">
                <form method="POST" :action="'/admin/inventory/' + selectedStock.id + '/record-damaged'" class="space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Product</span>
                        <span class="text-sm font-bold text-slate-900" x-text="selectedStock.product.name"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Detected In State <span class="text-rose-500">*</span></label>
                        <select name="source_state" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="empty">Empty Stock (Returned cracked / defective)</option>
                            <option value="full">Full Stock (Leaking / punctured handle)</option>
                            <option value="under_cleaning">Washing Bay (Failed pressure check)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Damaged Quantity <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" min="1" value="1" required class="w-full px-3 py-2 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Defect Reason <span class="text-rose-500">*</span></label>
                        <input type="text" name="reason" placeholder="e.g. Hairline crack on base; condemned for scrap" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" @click="damageModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 text-white text-xs font-bold rounded-xl hover:bg-rose-700">
                            Quarantine to Damaged Stock
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- Modal 3: Reconcile Physical Audit -->
    <div x-show="adjustModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="adjustModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 space-y-4" @click.away="adjustModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Physical Stock Count Reconciliation</h3>
                <button type="button" @click="adjustModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <template x-if="selectedStock">
                <form method="POST" :action="'/admin/inventory/' + selectedStock.id + '/adjust'" class="space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Reconciling SKU</span>
                        <span class="text-sm font-bold text-slate-900" x-text="selectedStock.product.name"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Full Stock Count</label>
                            <input type="number" name="full_stock" :value="selectedStock.full_stock" min="0" required class="w-full px-3 py-2 text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Empty Cans Count</label>
                            <input type="number" name="empty_stock" :value="selectedStock.empty_stock" min="0" required class="w-full px-3 py-2 text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Washing Bay Count</label>
                            <input type="number" name="under_cleaning" :value="selectedStock.under_cleaning" min="0" required class="w-full px-3 py-2 text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Damaged Count</label>
                            <input type="number" name="damaged_stock" :value="selectedStock.damaged_stock" min="0" required class="w-full px-3 py-2 text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Low-Stock Reorder Threshold</label>
                        <input type="number" name="reorder_level" :value="selectedStock.reorder_level" min="0" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Audit Reason / Justification <span class="text-rose-500">*</span></label>
                        <input type="text" name="reason" placeholder="e.g. Monthly physical floor count audit" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" @click="adjustModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800">
                            Save Audit Adjustment
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
