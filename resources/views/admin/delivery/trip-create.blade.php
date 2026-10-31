@extends('layouts.admin')

@section('title', 'Create Delivery Runsheet')

@section('page_title', 'Create Delivery Runsheet / Trip')

@section('page_actions')
<a href="{{ route('admin.delivery.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    &larr; Back to Fleet
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    driverType: 'staff',
    selectAll: true,
    toggleAll() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(cb => cb.checked = this.selectAll);
    }
}">
    <form method="POST" action="{{ route('admin.delivery.trips.store') }}" class="space-y-6">
        @csrf

        <!-- Trip Parameters Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">
                1. Route & Schedule Details
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Delivery Date *</label>
                    <input type="date" name="trip_date" value="{{ $selectedDate }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Delivery Slot Window *</label>
                    <select name="delivery_slot_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                        @foreach($slots as $slot)
                            <option value="{{ $slot->id }}" {{ $selectedSlotId == $slot->id ? 'selected' : '' }}>
                                {{ $slot->name }} ({{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Driver Type Selector -->
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Driver Assignment Type *</label>
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <label class="flex items-center gap-2 p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="radio" name="driver_type" value="staff" x-model="driverType" class="text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="font-bold text-slate-900 block">Own Staff Driver</span>
                            <span class="text-[10px] text-slate-500">Internal company delivery personnel</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="radio" name="driver_type" value="partner" x-model="driverType" class="text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="font-bold text-slate-900 block">3rd Party Delivery Partner</span>
                            <span class="text-[10px] text-slate-500">Outsourced logistics (Porter, Shadowfax, Dunzo)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Conditional Driver Dropdowns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                <div x-show="driverType === 'staff'">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Select Staff Driver *</label>
                    <select name="staff_profile_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Choose staff driver --</option>
                        @foreach($staffDrivers as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->user?->name }} (Vehicle: {{ $staff->vehicle_number ?? 'None' }})</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="driverType === 'partner'">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Select Delivery Partner *</label>
                    <select name="delivery_partner_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Choose partner vendor --</option>
                        @foreach($partners as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->contact_phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Vehicle Plate / Registration</label>
                    <input type="text" name="vehicle_number" placeholder="e.g. TN-01-AB-1234" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Route Dispatch Notes</label>
                <input type="text" name="notes" placeholder="e.g. North Chennai zone morning route; priority commercial delivery" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Orders Assignment Checklist Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">2. Assign Scheduled Orders to Runsheet</h3>
                    <p class="text-xs text-slate-500">Check the orders to include in this driver's route sequence</p>
                </div>
                <label class="flex items-center gap-1.5 text-xs font-bold text-brand-600 cursor-pointer">
                    <input type="checkbox" x-model="selectAll" @change="toggleAll()" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>Select All</span>
                </label>
            </div>

            @if($pendingOrders->isEmpty())
                <div class="py-8 text-center text-slate-400 text-xs">
                    No unassigned orders found for the selected date and slot. You can still create the empty runsheet now and attach orders later.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                                <th class="py-2.5 px-3 w-8"></th>
                                <th class="py-2.5 px-3">Order #</th>
                                <th class="py-2.5 px-3">Customer & Address</th>
                                <th class="py-2.5 px-3 text-right">Items / Volume</th>
                                <th class="py-2.5 px-3 text-right">Payment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pendingOrders as $ord)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-2.5 px-3">
                                        <input type="checkbox" name="order_ids[]" value="{{ $ord->id }}" checked class="order-checkbox rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-slate-900">
                                        {{ $ord->order_number }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-900">{{ $ord->snapshot_address['contact_name'] ?? $ord->customer?->user?->name }}</div>
                                        <div class="text-slate-500 text-[11px]">{{ $ord->snapshot_address['area'] ?? '' }}, {{ $ord->snapshot_address['city'] ?? '' }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800">
                                        {{ $ord->items->sum('quantity') }} cans
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <span class="font-mono font-bold text-slate-900 block">₹{{ number_format($ord->total_amount, 2) }}</span>
                                        <span class="text-[10px] uppercase text-slate-500">{{ $ord->payment_method_preferred }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.delivery.index') }}" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition-colors">
                Generate Delivery Runsheet
            </button>
        </div>
    </form>
</div>
@endsection
