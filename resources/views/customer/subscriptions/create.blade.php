@extends('layouts.customer')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    frequency: 'daily',
    selectedProduct: '{{ $products->first()?->id ?? '' }}',
    quantity: 1,
    products: {{ $products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float)$p->selling_price, 'capacity' => $p->capacity_value . ' ' . $p->capacity_unit])->toJson() }}
}">
    <!-- Breadcrumbs -->
    <div>
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('customer.dashboard') }}" class="hover:text-brand-600">Home</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('customer.subscriptions.index') }}" class="hover:text-brand-600">Subscriptions</a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-semibold">New Plan</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Set Up Recurring Water Subscription</h1>
        <p class="text-sm text-slate-500 mt-1">Configure your automatic delivery cadence. Fresh cans delivered straight to your doorstep.</p>
    </div>

    <form action="{{ route('customer.subscriptions.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Choose Product & Quantity -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs flex items-center justify-center font-bold">1</span>
                Select Water Can Product
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Water Can Variant *</label>
                    <select name="items[0][product_id]" x-model="selectedProduct" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} — ₹{{ number_format($product->selling_price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Cans per Delivery *</label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="items[0][quantity]" x-model="quantity" min="1" max="20" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500" required>
                        <span class="text-xs text-slate-500 whitespace-nowrap">can(s)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Delivery Cadence & Schedule -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs flex items-center justify-center font-bold">2</span>
                Delivery Cadence & Schedule
            </h2>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Delivery Frequency *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer text-center transition-all"
                           :class="frequency === 'daily' ? 'border-brand-600 bg-brand-50/50 text-brand-900 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="frequency" value="daily" x-model="frequency" class="sr-only">
                        <span class="text-xs font-bold">Every Day</span>
                        <span class="text-[10px] text-slate-500 mt-0.5">Daily delivery</span>
                    </label>

                    <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer text-center transition-all"
                           :class="frequency === 'alternate_days' ? 'border-brand-600 bg-brand-50/50 text-brand-900 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="frequency" value="alternate_days" x-model="frequency" class="sr-only">
                        <span class="text-xs font-bold">Alternate Days</span>
                        <span class="text-[10px] text-slate-500 mt-0.5">Every 2nd day</span>
                    </label>

                    <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer text-center transition-all"
                           :class="frequency === 'weekly' ? 'border-brand-600 bg-brand-50/50 text-brand-900 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="frequency" value="weekly" x-model="frequency" class="sr-only">
                        <span class="text-xs font-bold">Weekly</span>
                        <span class="text-[10px] text-slate-500 mt-0.5">Once a week</span>
                    </label>

                    <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer text-center transition-all"
                           :class="frequency === 'custom_days' ? 'border-brand-600 bg-brand-50/50 text-brand-900 ring-2 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="frequency" value="custom_days" x-model="frequency" class="sr-only">
                        <span class="text-xs font-bold">Custom Days</span>
                        <span class="text-[10px] text-slate-500 mt-0.5">Specific weekdays</span>
                    </label>
                </div>
            </div>

            <!-- Custom Days Checkboxes -->
            <div x-show="frequency === 'custom_days'" x-transition class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-xs font-bold text-slate-700 mb-2">Select Days of Delivery:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'] as $val => $dayName)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-medium text-slate-700 cursor-pointer hover:bg-slate-100">
                            <input type="checkbox" name="custom_days[]" value="{{ $val }}" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                            <span>{{ $dayName }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Start Date & End Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Starting Date *</label>
                    <input type="date" name="start_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">End Date (Optional)</label>
                    <input type="date" name="end_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500" placeholder="Ongoing indefinitely">
                    <p class="text-[11px] text-slate-400 mt-1">Leave empty for continuous recurring subscription.</p>
                </div>
            </div>

            <!-- Delivery Slot -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Preferred Delivery Window *</label>
                <select name="delivery_slot_id" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500" required>
                    @foreach($slots as $slot)
                        <option value="{{ $slot->id }}">
                            {{ $slot->name }} ({{ $slot->formatted_time_range }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Section 3: Delivery Address & Payment Method -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs flex items-center justify-center font-bold">3</span>
                Address & Payment
            </h2>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Delivery Address *</label>
                @if($addresses->isEmpty())
                    <p class="text-xs text-rose-600">No saved address found. Please add an address to your profile first.</p>
                @else
                    <select name="delivery_address_id" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500" required>
                        @foreach($addresses as $addr)
                            <option value="{{ $addr->id }}">
                                {{ $addr->contact_person_name }} — {{ $addr->house_flat_no }}, {{ $addr->building_apartment_name ?? '' }}, {{ $addr->street_address }}, {{ $addr->area }} ({{ $addr->pincode }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Payment Method *</label>
                    <select name="payment_method" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500" required>
                        <option value="cod">Cash on Delivery (COD)</option>
                        <option value="pay_later">Pay Later (Monthly Account Dues)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Delivery Instructions (Optional)</label>
                    <input type="text" name="customer_notes" maxlength="255" placeholder="e.g. Leave can outside door, ring bell" class="w-full text-sm rounded-xl border-slate-300 focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('customer.subscriptions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Confirm & Activate Subscription
            </button>
        </div>
    </form>
</div>
@endsection
