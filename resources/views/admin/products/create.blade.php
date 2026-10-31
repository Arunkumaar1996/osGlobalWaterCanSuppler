@extends('layouts.admin')

@section('title', 'Add Product - Admin')
@section('page_title', 'Create New Product')
@section('page_subtitle', 'Configure product specs, volume capacity, pricing, and exchange logic')

@section('header_actions')
<a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Catalog</span>
</a>
@endsection

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Product Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. 20L Purified Water Can" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 @error('name') border-rose-300 ring-2 ring-rose-100 @enderror">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">SKU Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="e.g. CAN-20L-STD" required class="w-full px-4 py-2.5 text-sm uppercase font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 @error('sku') border-rose-300 ring-2 ring-rose-100 @enderror">
                    @error('sku') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Category <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 @error('category_id') border-rose-300 ring-2 ring-rose-100 @enderror">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Can Container Type</label>
                    <select name="can_type_id" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">None / Non-returnable</option>
                        @foreach($canTypes as $canType)
                            <option value="{{ $canType->id }}" {{ old('can_type_id') == $canType->id ? 'selected' : '' }}>
                                {{ $canType->name }} ({{ $canType->capacity_litres }}L - Deposit: ₹{{ number_format($canType->deposit_price, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Links to empty-can container inventory tracking</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Short Description</label>
                <input type="text" name="short_description" value="{{ old('short_description') }}" placeholder="Quick summary for customer cards" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Description</label>
                <textarea name="description" rows="3" placeholder="Detailed product specifications..." class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Volume Capacity & Quantities</h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Capacity Value <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.1" name="capacity_value" value="{{ old('capacity_value', 20) }}" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unit</label>
                    <select name="capacity_unit" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="Litre" {{ old('capacity_unit') === 'Litre' ? 'selected' : '' }}>Litre</option>
                        <option value="ml" {{ old('capacity_unit') === 'ml' ? 'selected' : '' }}>ml</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Min Order Qty <span class="text-rose-500">*</span></label>
                    <input type="number" name="min_order_quantity" value="{{ old('min_order_quantity', 1) }}" min="1" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Max Order Qty <span class="text-rose-500">*</span></label>
                    <input type="number" name="max_order_quantity" value="{{ old('max_order_quantity', 50) }}" min="1" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Pricing, Tax & Rules</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Selling Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', '80.00') }}" required class="w-full px-4 py-2.5 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 @error('selling_price') border-rose-300 ring-2 ring-rose-100 @enderror">
                    @error('selling_price') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">GST Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate_percent" value="{{ old('tax_rate_percent', '18.00') }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_tax_inclusive" value="1" {{ old('is_tax_inclusive', true) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <span class="text-xs font-bold text-slate-700">Price is Inclusive of Tax</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="requires_can_exchange" value="1" {{ old('requires_can_exchange', true) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Requires 1:1 Can Exchange</span>
                            <span class="text-[11px] text-slate-400">Driver collects empty can upon delivery</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Featured on Homepage</span>
                            <span class="text-[11px] text-slate-400">Highlight on public landing page</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Active Status</span>
                            <span class="text-[11px] text-slate-400">Visible for customer ordering</span>
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
                Save Product
            </button>
        </div>
    </form>
</div>
@endsection
