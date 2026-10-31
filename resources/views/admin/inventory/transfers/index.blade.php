@extends('layouts.admin')

@section('title', 'Inter-Warehouse Transfers - Admin')
@section('page_title', 'Inter-Warehouse Stock Transfers')
@section('page_subtitle', 'Transfer full inventory, empty returned cans, or quarantine stock between branches and warehouses')

@section('header_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Inventory Hub</span>
    </a>
    <a href="{{ route('admin.inventory.transfers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white font-semibold text-sm hover:bg-brand-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Initiate New Transfer</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ receiveModal: false, selectedTransfer: null, openReceive(trf) { this.selectedTransfer = trf; this.receiveModal = true; } }">
    <!-- Transfers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Transfer No.</th>
                        <th class="px-6 py-3.5">Route (Origin &rarr; Destination)</th>
                        <th class="px-6 py-3.5">Items Dispatched</th>
                        <th class="px-6 py-3.5">Initiated Date</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @forelse($transfers as $trf)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-900">
                            {{ $trf->transfer_number }}
                            <span class="block text-[11px] text-slate-400 font-sans">By: {{ $trf->initiatedBy?->name ?? 'Staff' }}</span>
                        </td>
                        <td class="px-6 py-4 font-sans">
                            <div class="font-semibold text-slate-800">{{ $trf->sourceWarehouse?->name }}</div>
                            <div class="text-xs text-brand-600 font-bold">&darr; In transit to</div>
                            <div class="font-semibold text-slate-800">{{ $trf->destinationWarehouse?->name }}</div>
                        </td>
                        <td class="px-6 py-4 font-sans text-xs">
                            @foreach($trf->items as $item)
                                <div>
                                    <span class="font-bold text-slate-900">{{ $item->quantity_sent }}</span> cans of 
                                    <span class="font-semibold text-slate-700">{{ $item->product?->name }}</span>
                                    <span class="text-[10px] text-slate-400">({{ ucfirst($item->stock_type) }})</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-slate-700 font-sans text-xs">
                            {{ $trf->initiated_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4 font-sans">
                            @if($trf->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Completed
                                </span>
                            @elseif($trf->status === 'in_transit')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    In Transit
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ ucfirst($trf->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-sans">
                            @if($trf->status === 'in_transit')
                            <button type="button" @click="openReceive({{ json_encode($trf) }})" class="px-3 py-1.5 text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-lg transition-colors">
                                Receive Stock
                            </button>
                            @else
                            <span class="text-xs text-slate-400">Received {{ $trf->completed_at?->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-sans">
                            No inter-warehouse stock transfers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal: Receive Stock at Destination Warehouse -->
    <div x-show="receiveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="receiveModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 space-y-4" @click.away="receiveModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Receive In-Transit Transfer</h3>
                <button type="button" @click="receiveModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <template x-if="selectedTransfer">
                <form method="POST" :action="'/admin/inventory/transfers/' + selectedTransfer.id + '/complete'" class="space-y-4">
                    @csrf
                    <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
                        <div class="font-bold text-slate-900" x-text="selectedTransfer.transfer_number"></div>
                        <div class="text-slate-500">Destination: <span class="font-semibold text-slate-800" x-text="selectedTransfer.destination_warehouse.name"></span></div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, idx) in selectedTransfer.items" :key="item.id">
                            <div class="p-3 border border-slate-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-900">
                                    <span x-text="item.product.name"></span>
                                    <span class="text-brand-600" x-text="item.quantity_sent + ' cans sent'"></span>
                                </div>
                                <input type="hidden" :name="'items[' + idx + '][stock_transfer_item_id]'" :value="item.id">

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Accepted (Good)</label>
                                        <input type="number" :name="'items[' + idx + '][quantity_received]'" :value="item.quantity_sent" min="0" :max="item.quantity_sent" required class="w-full px-3 py-1.5 text-xs font-bold bg-emerald-50/50 border border-emerald-200 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Damaged in Transit</label>
                                        <input type="number" :name="'items[' + idx + '][quantity_damaged_in_transit]'" value="0" min="0" :max="item.quantity_sent" required class="w-full px-3 py-1.5 text-xs font-bold bg-rose-50/50 border border-rose-200 rounded-lg">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Receiving Remarks</label>
                        <input type="text" name="notes" placeholder="e.g. Delivered intact by logistics van TN-04-1234" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" @click="receiveModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700">
                            Confirm Delivery & Update Stock
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
