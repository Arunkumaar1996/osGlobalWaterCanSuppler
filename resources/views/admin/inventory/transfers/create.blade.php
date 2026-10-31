@extends('layouts.admin')

@section('title', 'Initiate Stock Transfer - Admin')
@section('page_title', 'Initiate Inter-Warehouse Transfer')
@section('page_subtitle', 'Dispatch water cans or empty containers from one facility to another')

@section('header_actions')
<a href="{{ route('admin.inventory.transfers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Transfers</span>
</a>
@endsection

@section('content')
<div class="max-w-4xl" x-data="transferForm()">
    <form method="POST" action="{{ route('admin.inventory.transfers.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Route Configuration</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Origin / Dispatch Warehouse <span class="text-rose-500">*</span></label>
                    <select name="source_warehouse_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Destination Warehouse <span class="text-rose-500">*</span></label>
                    <select name="destination_warehouse_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $loop->iteration == 2 ? 'selected' : '' }}>{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Transfer Notes / Vehicle Information</label>
                <input type="text" name="notes" placeholder="e.g. Scheduled morning shuttle van TN-04-AB-9876" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Transfer Line Items -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Transfer Items</h3>
                    <p class="text-xs text-slate-500">Choose SKU, stock category (Full, Empty, Damaged), and quantities to move</p>
                </div>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-50 text-brand-700 text-xs font-bold rounded-xl hover:bg-brand-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Item</span>
                </button>
            </div>

            @error('items')
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-semibold">
                    {{ $message }}
                </div>
            @enderror

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-5">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Product</label>
                            <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" required class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Stock Category</label>
                            <select :name="'items[' + index + '][stock_type]'" x-model="item.stock_type" required class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="full">Full Stock (Filled Cans)</option>
                                <option value="empty">Empty Stock (Returned Bottles)</option>
                                <option value="damaged">Damaged Stock (For Repair/Condemn)</option>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Quantity</label>
                            <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required class="w-full px-3 py-2 text-xs font-bold bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div class="md:col-span-1 flex items-center justify-end">
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-slate-400 hover:text-rose-500 p-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.inventory.transfers.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700 shadow-sm transition-colors">
                Dispatch Stock Transfer
            </button>
        </div>
    </form>
</div>

<script>
function transferForm() {
    return {
        items: [
            { product_id: '{{ $products->first()?->id }}', stock_type: 'full', quantity: 20 }
        ],
        addItem() {
            this.items.push({
                product_id: '{{ $products->first()?->id }}',
                stock_type: 'full',
                quantity: 10
            });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
