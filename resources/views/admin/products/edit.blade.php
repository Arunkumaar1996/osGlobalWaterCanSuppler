@extends('layouts.admin')

@section('title', 'Edit ' . $product->name . ' - Admin')
@section('page_title', 'Edit Product: ' . $product->name)
@section('page_subtitle', 'Update specifications, pricing, and view price modification audit trail')

@section('header_actions')
<a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Catalog</span>
</a>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Product Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SKU Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full px-4 py-2.5 text-sm uppercase font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('sku') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Can Container Type</label>
                    <select name="can_type_id" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">None / Non-returnable</option>
                        @foreach($canTypes as $canType)
                            <option value="{{ $canType->id }}" {{ old('can_type_id', $product->can_type_id) == $canType->id ? 'selected' : '' }}>
                                {{ $canType->name }} ({{ $canType->capacity_litres }}L - Deposit: ₹{{ number_format($canType->deposit_price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Short Description</label>
                <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Volume Capacity & Quantities</h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Capacity Value <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.1" name="capacity_value" value="{{ old('capacity_value', $product->capacity_value) }}" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unit</label>
                    <select name="capacity_unit" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="Litre" {{ old('capacity_unit', $product->capacity_unit) === 'Litre' ? 'selected' : '' }}>Litre</option>
                        <option value="ml" {{ old('capacity_unit', $product->capacity_unit) === 'ml' ? 'selected' : '' }}>ml</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Min Order Qty <span class="text-rose-500">*</span></label>
                    <input type="number" name="min_order_quantity" value="{{ old('min_order_quantity', $product->min_order_quantity) }}" min="1" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Max Order Qty <span class="text-rose-500">*</span></label>
                    <input type="number" name="max_order_quantity" value="{{ old('max_order_quantity', $product->max_order_quantity) }}" min="1" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Pricing, Tax & Price Change Logging</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Selling Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required class="w-full px-4 py-2.5 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Current: ₹{{ number_format($product->selling_price, 2) }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">GST Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate_percent" value="{{ old('tax_rate_percent', $product->tax_rate_percent) }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_tax_inclusive" value="1" {{ old('is_tax_inclusive', $product->is_tax_inclusive) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <span class="text-xs font-bold text-slate-700">Price is Inclusive of Tax</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Reason for Price Change (if price was modified)</label>
                <input type="text" name="price_change_reason" value="{{ old('price_change_reason') }}" placeholder="e.g. Fuel inflation adjustment, seasonal discount revised..." class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                <p class="text-[11px] text-slate-400 mt-1">If the selling price is altered, this reason and timestamp will be logged into the permanent price history ledger.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="requires_can_exchange" value="1" {{ old('requires_can_exchange', $product->requires_can_exchange) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Requires 1:1 Can Exchange</span>
                            <span class="text-[11px] text-slate-400">Driver collects empty can</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Featured on Homepage</span>
                            <span class="text-[11px] text-slate-400">Highlight on public page</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Active Status</span>
                            <span class="text-[11px] text-slate-400">Available for orders</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700 shadow-sm transition-colors">
                Update Product
            </button>
        </div>
    </form>

    <!-- Recent Price History Summary -->
    @if($product->priceHistories->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Recent Price Changes</h3>
            <a href="{{ route('admin.products.price-history', $product) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                View Full Audit Trail ({{ $product->priceHistories->count() }}) &rarr;
            </a>
        </div>
        <div class="divide-y divide-slate-100 text-xs mt-3">
            @foreach($product->priceHistories->take(3) as $history)
            <div class="py-2.5 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-slate-800">
                        ₹{{ number_format($history->old_price, 2) }} &rarr; <span class="font-bold text-brand-600">₹{{ number_format($history->new_price, 2) }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400">{{ $history->reason ?: 'No notes provided' }}</div>
                </div>
                <div class="text-right text-[11px] text-slate-400">
                    <div>{{ $history->effective_from->format('d M Y, h:i A') }}</div>
                    <div>By: {{ $history->changedBy?->name ?? 'System' }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
