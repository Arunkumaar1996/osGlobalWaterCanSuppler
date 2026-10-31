@extends('layouts.admin')

@section('title', 'Delivery Dispatch & Fleet')

@section('page_title', 'Delivery Dispatch & Fleet Management')

@section('page_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.delivery.partners.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Delivery Partners
    </a>
    <a href="{{ route('admin.delivery.slots.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Slots & Capacity
    </a>
    <a href="{{ route('admin.delivery.trips.create', ['date' => $selectedDate]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Delivery Runsheet
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Filter & Quick Switcher -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.delivery.index') }}" class="flex items-center gap-3 w-full sm:w-auto">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Scheduled Date:</span>
            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Filter
            </button>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.delivery.index', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $selectedDate === \Carbon\Carbon::today()->format('Y-m-d') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Today
            </a>
            <a href="{{ route('admin.delivery.index', ['date' => \Carbon\Carbon::tomorrow()->format('Y-m-d')]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $selectedDate === \Carbon\Carbon::tomorrow()->format('Y-m-d') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Tomorrow
            </a>
        </div>
    </div>

    <!-- Active Delivery Trips / Runsheets Table -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Scheduled Runsheets & Trips ({{ count($trips) }})</h3>
                <p class="text-xs text-slate-500">Route dispatches for {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}</p>
            </div>
            <a href="{{ route('admin.delivery.trips.create', ['date' => $selectedDate]) }}" class="text-xs font-bold text-brand-600 hover:underline">
                + New Runsheet
            </a>
        </div>

        @if($trips->isEmpty())
            <div class="py-10 text-center text-slate-400 text-xs">
                No delivery trips scheduled for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}. Click "Create Delivery Runsheet" to assign drivers and orders.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Trip #</th>
                            <th class="py-3 px-3">Slot Window</th>
                            <th class="py-3 px-3">Driver / Partner</th>
                            <th class="py-3 px-3">Vehicle</th>
                            <th class="py-3 px-3 text-center">Payload</th>
                            <th class="py-3 px-3 text-right">Cash Handover</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($trips as $trip)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                    <a href="{{ route('admin.delivery.trips.show', $trip->id) }}" class="text-brand-600 hover:underline">
                                        {{ $trip->trip_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-slate-700 font-medium">
                                    {{ $trip->deliverySlot?->name ?? 'Standard Window' }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $trip->driver_name }}</div>
                                    <span class="text-[10px] text-slate-500 uppercase">{{ $trip->driver_type === 'staff' ? 'Internal Staff' : '3rd Party Partner' }}</span>
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-700">
                                    {{ $trip->vehicle_number ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="font-mono font-bold text-slate-900">{{ $trip->stops->count() }} stops</span>
                                    <span class="text-[11px] text-slate-500 block">({{ $trip->total_litres_payload }}L)</span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600">
                                    ₹{{ number_format($trip->cash_collected, 2) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $trip->status === 'in_transit' ? 'bg-sky-100 text-sky-800 animate-pulse' :
                                          ($trip->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ str_replace('_', ' ', $trip->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('admin.delivery.trips.show', $trip->id) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors shadow-sm">
                                        Open Runsheet &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Unassigned Orders Awaiting Runsheet -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Unassigned Orders Waiting for Dispatch</h3>
                <p class="text-xs text-slate-500">Orders scheduled for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }} that have not yet been assigned to a driver</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $unassignedOrders->count() > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                {{ $unassignedOrders->count() }} Orders Pending
            </span>
        </div>

        @if($unassignedOrders->isEmpty())
            <div class="py-8 text-center text-slate-400 text-xs">
                All scheduled orders for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }} have been assigned to delivery runsheets!
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-2.5 px-3">Order #</th>
                            <th class="py-2.5 px-3">Slot</th>
                            <th class="py-2.5 px-3">Customer & Contact</th>
                            <th class="py-2.5 px-3">Delivery Area</th>
                            <th class="py-2.5 px-3 text-right">Items / Volume</th>
                            <th class="py-2.5 px-3 text-right">Total Payable</th>
                            <th class="py-2.5 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($unassignedOrders as $uOrd)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-3 font-bold text-slate-900">
                                    <a href="{{ route('admin.orders.show', $uOrd->id) }}" class="text-brand-600 hover:underline">
                                        {{ $uOrd->order_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 font-medium text-slate-700">
                                    {{ $uOrd->deliverySlot?->name ?? 'Standard' }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-slate-900">{{ $uOrd->snapshot_address['contact_name'] ?? $uOrd->customer?->user?->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $uOrd->snapshot_address['contact_mobile'] ?? 'N/A' }}</div>
                                </td>
                                <td class="py-2.5 px-3 text-slate-600">
                                    {{ $uOrd->snapshot_address['area'] ?? '' }} - {{ $uOrd->snapshot_address['pincode'] ?? '' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800">
                                    {{ $uOrd->items->sum('quantity') }} cans
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                                    ₹{{ number_format($uOrd->total_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ route('admin.delivery.trips.create', ['date' => $selectedDate, 'slot_id' => $uOrd->delivery_slot_id]) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-[11px] shadow-sm transition-colors">
                                        Assign to Runsheet &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
