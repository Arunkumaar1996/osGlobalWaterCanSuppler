@extends('layouts.staff')

@section('title', 'My Deliveries')

@section('content')
<div class="space-y-4" x-data="{
    otpModalOpen: false,
    failModalOpen: false,
    activeStopId: null,
    activeOrderNumber: '',
    suggestedEmpty: 0,
    codAmount: 0.00,
    openVerify(stopId, ordNum, emptyCount, cod) {
        this.activeStopId = stopId;
        this.activeOrderNumber = ordNum;
        this.suggestedEmpty = emptyCount;
        this.codAmount = cod;
        this.otpModalOpen = true;
    },
    openFail(stopId, ordNum) {
        this.activeStopId = stopId;
        this.activeOrderNumber = ordNum;
        this.failModalOpen = true;
    }
}">
    <!-- Today's Run Overview -->
    <div class="bg-slate-800/90 rounded-2xl p-4 border border-slate-700 shadow-md">
        <div class="flex items-center justify-between pb-3 border-b border-slate-700/60">
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-brand-400">Today's Route</span>
                <h2 class="text-base font-extrabold text-white">
                    {{ $trip ? $trip->trip_number : 'Assigned Deliveries' }}
                </h2>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $pendingCount > 0 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                {{ $pendingCount }} Pending
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2 mt-3 text-center">
            <div class="p-2 rounded-xl bg-slate-900/60 border border-slate-700/40">
                <span class="text-[10px] text-slate-400 block font-semibold">Assigned</span>
                <span class="text-base font-black text-white">{{ $stops->count() }}</span>
            </div>
            <div class="p-2 rounded-xl bg-slate-900/60 border border-slate-700/40">
                <span class="text-[10px] text-emerald-400 block font-semibold">Delivered</span>
                <span class="text-base font-black text-emerald-400">{{ $deliveredCount }}</span>
            </div>
            <div class="p-2 rounded-xl bg-slate-900/60 border border-slate-700/40">
                <span class="text-[10px] text-rose-400 block font-semibold">Failed</span>
                <span class="text-base font-black text-rose-400">{{ $failedCount }}</span>
            </div>
        </div>
    </div>

    @if(!$trip || $stops->isEmpty())
        <!-- Empty Deliveries State -->
        <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-800 text-center">
            <div class="w-14 h-14 rounded-full bg-slate-700/50 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
            <h3 class="text-sm font-bold text-white">No Deliveries Assigned Yet</h3>
            <p class="text-xs text-slate-400 mt-1">
                When trips are dispatched to your vehicle, they will appear here with customer contacts, navigation, and OTP verification.
            </p>
        </div>
    @else
        <!-- Stops List -->
        <div class="space-y-3">
            @foreach($stops as $stop)
                @php
                    $ord = $stop->order;
                    $totalCans = $ord->items->sum('quantity');
                    $codRemaining = ($ord->payment_status !== 'paid') ? max(0, (float) $ord->total_amount - (float) $ord->paid_amount) : 0.00;
                    $phone = $ord->snapshot_address['contact_mobile'] ?? 'N/A';
                    $addressString = urlencode(($ord->snapshot_address['house_flat_no'] ?? '') . ' ' . ($ord->snapshot_address['street'] ?? '') . ' ' . ($ord->snapshot_address['area'] ?? '') . ' ' . ($ord->snapshot_address['city'] ?? '') . ' ' . ($ord->snapshot_address['pincode'] ?? ''));
                @endphp
                <div class="bg-slate-800/80 rounded-2xl p-4 border {{ $stop->status === 'delivered' ? 'border-emerald-800 bg-emerald-950/20' : 'border-slate-700' }} shadow-sm space-y-3">
                    <!-- Stop Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-xs">
                                {{ $stop->sequence_order }}
                            </span>
                            <div>
                                <span class="font-mono text-xs font-bold text-slate-300">#{{ $ord->order_number }}</span>
                                <h4 class="text-sm font-bold text-white leading-tight mt-0.5">
                                    {{ $ord->snapshot_address['contact_name'] ?? $ord->customer?->user?->name }}
                                </h4>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $stop->status === 'delivered' ? 'bg-emerald-500/20 text-emerald-300' :
                              ($stop->status === 'failed' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-700 text-slate-300') }}">
                            {{ $stop->status }}
                        </span>
                    </div>

                    <!-- Address & Landmark -->
                    <div class="text-xs text-slate-300 bg-slate-900/60 p-2.5 rounded-xl border border-slate-700/50 space-y-1">
                        <p class="leading-relaxed">
                            {{ $ord->snapshot_address['house_flat_no'] ?? '' }}, {{ $ord->snapshot_address['building'] ?? '' }}, {{ $ord->snapshot_address['street'] ?? '' }}, {{ $ord->snapshot_address['area'] ?? '' }} - {{ $ord->snapshot_address['pincode'] ?? '' }}
                        </p>
                        @if(!empty($ord->snapshot_address['landmark']))
                            <p class="text-[11px] text-amber-400 font-medium">Landmark: {{ $ord->snapshot_address['landmark'] }}</p>
                        @endif
                    </div>

                    <!-- Items & COD Due Bar -->
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-700/40">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Cans to Deliver</span>
                            <span class="font-mono font-bold text-white text-sm">{{ $totalCans }} cans</span>
                        </div>

                        <div class="text-right">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Payment</span>
                            @if($codRemaining > 0)
                                <span class="font-mono font-bold text-amber-400 text-sm">Collect ₹{{ number_format($codRemaining, 2) }}</span>
                            @else
                                <span class="text-[11px] font-bold text-emerald-400">Paid Online &check;</span>
                            @endif
                        </div>
                    </div>

                    <!-- Can Exchange Status Badge for Staff Driver -->
                    <div class="flex items-center gap-2 pt-0.5">
                        @if($ord->has_empty_cans_to_exchange)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-900/60 text-cyan-300 border border-cyan-700/50">
                                1:1 Exchange &bull; Collect {{ $ord->empty_cans_expected ?: $totalCans }} empties (Equal cans)
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-900/60 text-amber-300 border border-amber-700/50">
                                🆕 New Customer &bull; 0 empties to collect ({{ $ord->deposit_amount > 0 ? 'Deposit Paid' : 'Water Only' }})
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons Bar -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <!-- Call Button -->
                        <a href="tel:{{ $phone }}" class="flex items-center justify-center gap-1 py-2 px-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Call
                        </a>

                        <!-- Maps Navigation -->
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $addressString }}" target="_blank" class="flex items-center justify-center gap-1 py-2 px-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            Map
                        </a>

                        <!-- Verify OTP & Complete -->
                        @if($stop->status !== 'delivered')
                            <button type="button" @click="openVerify('{{ $stop->id }}', '{{ $ord->order_number }}', '{{ $ord->has_empty_cans_to_exchange ? ($ord->empty_cans_expected ?: $totalCans) : 0 }}', '{{ $codRemaining }}')" class="flex items-center justify-center gap-1 py-2 px-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md transition-colors">
                                Deliver
                            </button>
                        @else
                            <div class="flex items-center justify-center text-[11px] font-bold text-emerald-400">
                                &check; Done
                            </div>
                        @endif
                    </div>

                    @if($stop->status !== 'delivered')
                        <div class="text-right pt-1">
                            <button type="button" @click="openFail('{{ $stop->id }}', '{{ $ord->order_number }}')" class="text-[11px] font-semibold text-rose-400 hover:underline">
                                Report Issue / Reschedule &rarr;
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODAL: OTP Verification & Handover Completion -->
    <div x-show="otpModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-4">
        <div @click.away="otpModalOpen = false" class="bg-slate-900 border border-slate-700 rounded-3xl max-w-sm w-full p-5 shadow-2xl space-y-4 text-slate-100">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Doorstep Delivery Handover</h3>
                <button type="button" @click="otpModalOpen = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-slate-400">
                Order <strong class="text-white" x-text="'#' + activeOrderNumber"></strong> OTP Verification:
            </p>

            <form :action="'/staff/stops/' + activeStopId + '/verify'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-300 uppercase tracking-wider mb-1">Enter Customer 4-digit OTP *</label>
                    <input type="text" name="otp" required placeholder="OTP" class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-center font-mono text-xl font-bold tracking-widest text-white focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-300 uppercase tracking-wider mb-1">Empty Cans Collected (1:1 Exchange)</label>
                    <input type="number" min="0" name="empty_cans_collected" :value="suggestedEmpty" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-sm font-bold text-white focus:ring-2 focus:ring-brand-500">
                </div>

                <div x-show="codAmount > 0">
                    <label class="block font-bold text-slate-300 uppercase tracking-wider mb-1">Cash Collected (₹)</label>
                    <input type="number" step="0.01" min="0" name="cash_collected" :value="codAmount" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-sm font-bold text-white focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-300 uppercase tracking-wider mb-1">Proof / Delivery Note</label>
                    <input type="text" name="notes" placeholder="e.g. Handed over directly to resident" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-xs text-white focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="otpModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold shadow-md">
                        Confirm Handover
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Report Failure / Reschedule -->
    <div x-show="failModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-4">
        <div @click.away="failModalOpen = false" class="bg-slate-900 border border-slate-700 rounded-3xl max-w-sm w-full p-5 shadow-2xl space-y-4 text-slate-100">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white">Report Delivery Issue</h3>
                <button type="button" @click="failModalOpen = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
            </div>

            <form :action="'/staff/stops/' + activeStopId + '/fail'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-300 uppercase tracking-wider mb-1">Failure Reason *</label>
                    <select name="failure_reason" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-xs font-semibold text-white focus:ring-2 focus:ring-brand-500">
                        <option value="Customer house locked">Customer house locked</option>
                        <option value="Customer phone unreachable / switched off">Customer phone unreachable</option>
                        <option value="Customer requested reschedule">Customer requested reschedule</option>
                        <option value="Incorrect address provided">Incorrect address provided</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="reschedule" value="1" id="reschedule_box" checked class="rounded border-slate-700 text-brand-600 focus:ring-brand-500 bg-slate-950">
                    <label for="reschedule_box" class="font-bold text-slate-300">Reschedule for next slot</label>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="failModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold shadow-md">
                        Report Issue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
