@extends('layouts.admin')

@section('title', 'Runsheet ' . $trip->trip_number)

@section('page_title', 'Delivery Runsheet: ' . $trip->trip_number)

@section('page_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.delivery.index', ['date' => $trip->trip_date->format('Y-m-d')]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
        &larr; Back to Fleet
    </a>

    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print Runsheet
    </button>

    @if($trip->status === 'scheduled')
        <form method="POST" action="{{ route('admin.delivery.trips.dispatch', $trip->id) }}" onsubmit="return confirm('Dispatch this route runsheet out for delivery?')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Dispatch Out for Delivery
            </button>
        </form>
    @elseif($trip->status === 'in_transit')
        <form method="POST" action="{{ route('admin.delivery.trips.complete', $trip->id) }}" onsubmit="return confirm('Mark this entire route trip as completed?')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-sm">
                &check; Complete Runsheet Trip
            </button>
        </form>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    otpModalOpen: false,
    activeStopId: null,
    activeOrderNumber: '',
    suggestedEmpty: 0,
    codAmount: 0.00,
    openOtpModal(stopId, ordNum, emptyCount, cod) {
        this.activeStopId = stopId;
        this.activeOrderNumber = ordNum;
        this.suggestedEmpty = emptyCount;
        this.codAmount = cod;
        this.otpModalOpen = true;
    }
}">
    <!-- Top Runsheet Overview Summary Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Runsheet Manifest</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                        {{ $trip->status === 'in_transit' ? 'bg-sky-100 text-sky-800 animate-pulse' :
                          ($trip->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                        {{ str_replace('_', ' ', $trip->status) }}
                    </span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $trip->trip_number }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Scheduled for <strong class="text-slate-800">{{ $trip->trip_date->format('l, d F Y') }}</strong> &bull;
                    Slot: <strong class="text-slate-800">{{ $trip->deliverySlot?->name }} ({{ \Carbon\Carbon::parse($trip->deliverySlot?->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($trip->deliverySlot?->end_time)->format('h:i A') }})</strong>
                </p>
            </div>

            <!-- Handover Live Metrics -->
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center min-w-[100px]">
                    <span class="text-slate-400 font-bold block text-[10px] uppercase">Stops</span>
                    <span class="text-lg font-black text-slate-900">{{ $trip->stops->count() }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center min-w-[100px]">
                    <span class="text-slate-400 font-bold block text-[10px] uppercase">Payload</span>
                    <span class="text-lg font-black text-brand-600">{{ $trip->total_litres_payload }}L</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center min-w-[110px]">
                    <span class="text-slate-400 font-bold block text-[10px] uppercase">Cash Collected</span>
                    <span class="text-lg font-black text-emerald-600 font-mono">₹{{ number_format($trip->cash_collected, 2) }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center min-w-[110px]">
                    <span class="text-slate-400 font-bold block text-[10px] uppercase">Empties Picked</span>
                    <span class="text-lg font-black text-cyan-600 font-mono">{{ $trip->empty_cans_collected }} cans</span>
                </div>
            </div>
        </div>

        <!-- Driver & Vehicle Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 text-xs text-slate-600">
            <div>
                <span class="text-slate-400 uppercase font-bold text-[10px] block">Driver Assigned</span>
                <span class="font-bold text-slate-900 text-sm">{{ $trip->driver_name }}</span>
                <span class="text-slate-500 block text-[11px]">{{ $trip->driver_phone ?? 'Phone N/A' }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-bold text-[10px] block">Vehicle Plate</span>
                <span class="font-mono font-bold text-slate-900 text-sm">{{ $trip->vehicle_number ?? 'Not specified' }}</span>
                <span class="text-slate-500 block text-[11px]">{{ $trip->driver_type === 'staff' ? 'Company Fleet' : 'Partner Vehicle' }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-bold text-[10px] block">Dispatcher Notes</span>
                <span class="text-slate-700 italic">{{ $trip->notes ?? 'No route notes provided' }}</span>
            </div>
        </div>
    </div>

    <!-- Stop-by-Stop Sequential Route Stops Table -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Delivery Route Stops ({{ $trip->stops->count() }})</h3>
                <p class="text-xs text-slate-500">Sequential stops order with delivery verification OTPs and handover tracking</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3 text-center">Stop #</th>
                        <th class="py-3 px-3">Order & Customer</th>
                        <th class="py-3 px-3">Delivery Address & Landmark</th>
                        <th class="py-3 px-3">Items & Cans</th>
                        <th class="py-3 px-3 text-center">Verification OTP</th>
                        <th class="py-3 px-3 text-right">Payment Due</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($trip->stops as $stop)
                        @php
                            $ord = $stop->order;
                            $totalCans = $ord->items->sum('quantity');
                            $codRemaining = ($ord->payment_status !== 'paid') ? max(0, (float) $ord->total_amount - (float) $ord->paid_amount) : 0.00;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $stop->status === 'delivered' ? 'bg-emerald-50/20' : '' }}">
                            <td class="py-3 px-3 text-center font-bold font-mono text-slate-500">
                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-800 inline-flex items-center justify-center text-xs font-bold">
                                    {{ $stop->sequence_order }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <a href="{{ route('admin.orders.show', $ord->id) }}" class="font-bold font-mono text-brand-600 hover:underline">
                                    {{ $ord->order_number }}
                                </a>
                                <div class="font-bold text-slate-900 mt-0.5">{{ $ord->snapshot_address['contact_name'] ?? $ord->customer?->user?->name }}</div>
                                <a href="tel:{{ $ord->snapshot_address['contact_mobile'] ?? '' }}" class="text-[11px] text-slate-500 hover:text-brand-600">
                                    {{ $ord->snapshot_address['contact_mobile'] ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="py-3 px-3 text-slate-600 max-w-xs">
                                <div>{{ $ord->snapshot_address['house_flat_no'] ?? '' }}, {{ $ord->snapshot_address['building'] ?? '' }}, {{ $ord->snapshot_address['street'] ?? '' }}</div>
                                <div class="font-medium text-slate-800">{{ $ord->snapshot_address['area'] ?? '' }} - {{ $ord->snapshot_address['pincode'] ?? '' }}</div>
                                @if(!empty($ord->snapshot_address['landmark']))
                                    <div class="text-[10px] text-amber-700">Landmark: {{ $ord->snapshot_address['landmark'] }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-mono">
                                <strong class="text-slate-900">{{ $totalCans }} cans</strong>
                                <div class="text-[11px] text-slate-500">
                                    @foreach($ord->items as $it)
                                        <div>{{ $it->quantity }}x {{ $it->product_name_snapshot }}</div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-900 text-white font-mono font-bold tracking-widest text-xs shadow-sm">
                                    {{ $stop->delivery_otp }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($codRemaining > 0)
                                    <span class="font-mono font-bold text-rose-600 text-sm">₹{{ number_format($codRemaining, 2) }}</span>
                                    <span class="text-[10px] uppercase font-bold text-amber-600 block">Collect Cash</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Paid Online &check;
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $stop->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                                      ($stop->status === 'failed' ? 'bg-rose-100 text-rose-800' :
                                      ($stop->status === 'rescheduled' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600')) }}">
                                    {{ $stop->status }}
                                </span>
                                @if($stop->status === 'delivered')
                                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $stop->empty_cans_collected }} empties</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($stop->status !== 'delivered')
                                    <button type="button" @click="openOtpModal('{{ $stop->id }}', '{{ $ord->order_number }}', '{{ $totalCans }}', '{{ $codRemaining }}')" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm transition-colors">
                                        Verify & Deliver
                                    </button>
                                @else
                                    <span class="text-xs font-bold text-emerald-600">&check; Completed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Verify OTP & Complete Stop Delivery -->
    <div x-show="otpModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="otpModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Verify Doorstep Delivery Handover</h3>
                <button type="button" @click="otpModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-slate-600">
                Order <strong class="text-slate-900" x-text="activeOrderNumber"></strong> delivery completion:
            </p>

            <form :action="'/admin/delivery/stops/' + activeStopId + '/complete'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Enter Delivery OTP PIN *</label>
                    <input type="text" name="otp" required placeholder="4-digit OTP" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-center font-mono text-lg font-bold tracking-widest text-slate-900 focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Ask customer for the OTP sent to their mobile/dashboard (or master bypass: 9999).</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Empty Cans Returned by Customer (1:1 Exchange)</label>
                    <input type="number" min="0" name="empty_cans_collected" :value="suggestedEmpty" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">If customer returns fewer empties, their container holding balance will increase.</span>
                </div>

                <div x-show="codAmount > 0">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">COD Cash Collected (₹)</label>
                    <input type="number" step="0.01" min="0" name="cash_collected" :value="codAmount" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Delivery Notes / Proof</label>
                    <input type="text" name="notes" placeholder="e.g. Handed over to security guard / door delivery" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="otpModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md transition-colors">
                        Confirm Delivery & Handover
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
