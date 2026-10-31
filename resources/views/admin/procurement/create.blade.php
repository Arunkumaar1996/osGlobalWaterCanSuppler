@extends('layouts.admin')

@section('title', 'Create Purchase Order - Admin')
@section('page_title', 'Create Purchase Order')
@section('page_subtitle', 'Order purified water cans or container stock from registered suppliers')

@section('header_actions')
<a href="{{ route('admin.procurement.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to POs</span>
</a>
@endsection

@section('content')
<div class="max-w-4xl" x-data="poForm()">
    <form method="POST" action="{{ route('admin.procurement.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Procurement Header Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Supplier <span class="text-rose-500">*</span></label>
                    <select name="supplier_id" x-model="selectedSupplierId" @change="onSupplierChange()" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 @error('supplier_id') border-rose-300 ring-2 ring-rose-100 @enderror">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (old('supplier_id', $selectedSupplierId) == $supplier->id) ? 'selected' : '' }}>
                                {{ $supplier->company_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Branch <span class="text-rose-500">*</span></label>
                    <select name="branch_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Destination Warehouse <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Expected Delivery Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">PO Notes / Special Instructions</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Ensure batch manufacturing seal is intact" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Order Items</h3>
                    <p class="text-xs text-slate-500">Specify water cans, quantities, and agreed purchase price</p>
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
                            <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductSelect(index)" required class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="">Select Product</option>
                                <template x-for="p in availableProducts" :key="p.id">
                                    <option :value="p.id" x-text="p.name + ' (' + p.sku + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Quantity</label>
                            <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Unit Cost (₹)</label>
                            <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" min="0" required class="w-full px-3 py-2 text-xs font-bold bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div class="md:col-span-2 flex items-center justify-between">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block uppercase">Subtotal</span>
                                <span class="font-bold text-xs text-slate-900" x-text="'₹' + (item.quantity * item.unit_price).toFixed(2)"></span>
                            </div>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-slate-400 hover:text-rose-500 p-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase">Estimated Total Amount</span>
                <span class="text-xl font-black text-slate-900" x-text="'₹' + calculateTotal().toFixed(2)">₹0.00</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.procurement.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700 shadow-sm transition-colors">
                Issue Purchase Order
            </button>
        </div>
    </form>
</div>

@php
    $suppliersData = $suppliers->map(function ($s) {
        return [
            'id' => $s->id,
            'name' => $s->company_name,
            'products' => $s->supplierProducts->map(function ($sp) {
                return [
                    'id' => $sp->product_id,
                    'name' => $sp->product?->name ?? 'Product',
                    'sku' => $sp->product?->sku ?? '',
                    'purchase_price' => (float)$sp->purchase_price,
                ];
            }),
        ];
    });
@endphp

<script>
function poForm() {
    return {
        selectedSupplierId: '{{ old("supplier_id", $selectedSupplierId ?? "") }}',
        suppliersData: @json($suppliersData),
        availableProducts: [],
        items: [
            { product_id: '', quantity: 50, unit_price: 0.00 }
        ],
        init() {
            if (this.selectedSupplierId) {
                this.onSupplierChange();
            }
        },
        onSupplierChange() {
            const supplier = this.suppliersData.find(s => s.id == this.selectedSupplierId);
            if (supplier && supplier.products.length > 0) {
                this.availableProducts = supplier.products;
                if (this.items.length === 1 && !this.items[0].product_id) {
                    this.items[0].product_id = this.availableProducts[0].id;
                    this.items[0].unit_price = this.availableProducts[0].purchase_price;
                }
            } else {
                this.availableProducts = [];
            }
        },
        onProductSelect(index) {
            const prod = this.availableProducts.find(p => p.id == this.items[index].product_id);
            if (prod) {
                this.items[index].unit_price = prod.purchase_price;
            }
        },
        addItem() {
            const firstProd = this.availableProducts[0] || null;
            this.items.push({
                product_id: firstProd ? firstProd.id : '',
                quantity: 20,
                unit_price: firstProd ? firstProd.purchase_price : 0.00
            });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        calculateTotal() {
            return this.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0);
        }
    }
}
</script>
@endsection
