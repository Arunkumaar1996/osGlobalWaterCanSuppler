@extends('layouts.admin')

@section('title', 'Delivery Slots & Capacity Engine')

@section('page_title', 'Delivery Slots & Fleet Capacity Engine')

@section('page_actions')
<a href="{{ route('admin.delivery.index', ['date' => $date]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    &larr; Back to Fleet
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Date Selector Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.delivery.slots.index') }}" class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inspect Date:</span>
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                View Capacity
            </button>
        </form>

        <p class="text-xs text-slate-500">
            Capacity is dynamically checked against both order stops count and vehicle litre volume at checkout.
        </p>
    </div>

    <!-- Slots Capacity Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($slots as $slot)
            @php
                $cap = $capacities->get($slot->id);
                $ordersCount = $cap ? $cap->reserved_orders_count : 0;
                $litresCount = $cap ? $cap->reserved_litres_volume : 0;
                $isBlocked = $cap ? $cap->is_blocked : false;
                $orderPct = min(100, round(($ordersCount / max(1, $slot->max_orders_capacity)) * 100));
                $litrePct = min(100, round(($litresCount / max(1, $slot->max_litres_capacity)) * 100));
            @endphp
            <div class="bg-white rounded-3xl border {{ $isBlocked ? 'border-rose-200 bg-rose-50/10' : 'border-slate-200' }} p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900">{{ $slot->name }}</h3>
                            @if($isBlocked)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-800">
                                    Blocked for {{ \Carbon\Carbon::parse($date)->format('d M') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Open
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">
                            {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} &mdash; {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                            &bull; Cutoff Lead: {{ $slot->cutoff_lead_minutes }} mins
                        </p>
                    </div>

                    <!-- Block / Unblock Action Form -->
                    <form method="POST" action="{{ route('admin.delivery.slots.toggle-block', $slot->id) }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors {{ $isBlocked ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm' : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200' }}">
                            {{ $isBlocked ? 'Unblock Slot' : 'Block Slot' }}
                        </button>
                    </form>
                </div>

                <!-- Capacity Visual Gauges -->
                <div class="space-y-3 pt-2 text-xs">
                    <!-- Orders Capacity -->
                    <div class="space-y-1">
                        <div class="flex justify-between font-bold text-slate-700">
                            <span>Booked Delivery Stops:</span>
                            <span>{{ $ordersCount }} / {{ $slot->max_orders_capacity }} orders ({{ $orderPct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $orderPct >= 90 ? 'bg-rose-500' : ($orderPct >= 70 ? 'bg-amber-500' : 'bg-brand-600') }}" style="width: {{ $orderPct }}%"></div>
                        </div>
                    </div>

                    <!-- Litres Payload Capacity -->
                    <div class="space-y-1">
                        <div class="flex justify-between font-bold text-slate-700">
                            <span>Vehicle Litres Payload:</span>
                            <span>{{ $litresCount }} / {{ $slot->max_litres_capacity }} L ({{ $litrePct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $litrePct >= 90 ? 'bg-rose-500' : ($litrePct >= 70 ? 'bg-amber-500' : 'bg-cyan-600') }}" style="width: {{ $litrePct }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Base Capacity Limits</span>
                    <span>Max: {{ $slot->max_orders_capacity }} Stops &bull; {{ $slot->max_litres_capacity }} Litres</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
