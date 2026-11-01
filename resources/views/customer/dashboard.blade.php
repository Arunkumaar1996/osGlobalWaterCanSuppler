@extends('layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')
<div class="space-y-6">
    <!-- PWA App Header Card: Welcome Banner -->
    <div class="bg-gradient-to-r from-brand-600 via-brand-700 to-cyan-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-600/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    Pure Packaged Drinking Water
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-400/20 text-emerald-200 border border-emerald-400/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ session('active_branch_name', 'Chennai Hub') }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome, {{ auth()->user()->name }}!</h1>
            <p class="text-brand-100 text-xs sm:text-sm mt-1 max-w-xl">
                Default Address: <span class="font-medium text-white">{{ auth()->user()->customerProfile?->defaultAddress?->formatted_address ?? 'No address set' }}</span>
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <a href="{{ route('home') }}#products" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-white text-brand-700 hover:bg-brand-50 font-extrabold text-sm shadow-md hover-lift transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Order Water Cans
            </a>
        </div>
    </div>

    <!-- Live Ongoing Delivery Card (PWA Real-Time Tracker) -->
    @if(isset($activeOrder) && $activeOrder)
        @php
            $latestStop = $activeOrder->tripStops?->first();
            $driver = $latestStop?->trip?->staffProfile?->user ?? $latestStop?->trip?->driverUser;
            $driverPhone = $driver?->mobile ?? $driver?->phone;
            $otpCode = $latestStop?->delivery_otp ?? substr($activeOrder->order_number, -4);
        @endphp
        <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white rounded-3xl p-5 sm:p-6 border border-sky-800/50 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sky-400 block">Live Delivery in Progress</span>
                        <h2 class="text-sm font-extrabold text-white">Order #{{ $activeOrder->order_number }}</h2>
                    </div>
                </div>

                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                    {{ $activeOrder->delivery_status === 'out_for_delivery' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-sky-500/20 text-sky-300 border border-sky-500/40' }}">
                    {{ str_replace('_', ' ', $activeOrder->delivery_status) }}
                </span>
            </div>

            <!-- Visual Step Progress Bar -->
            <div class="grid grid-cols-3 gap-2 text-center text-[11px] font-semibold">
                <div class="p-2 rounded-xl {{ in_array($activeOrder->delivery_status, ['pending', 'assigned', 'out_for_delivery']) ? 'bg-sky-500/20 text-sky-300 border border-sky-500/30 font-bold' : 'bg-slate-800/50 text-slate-500' }}">
                    1. Confirmed &check;
                </div>
                <div class="p-2 rounded-xl {{ in_array($activeOrder->delivery_status, ['assigned', 'out_for_delivery']) ? 'bg-sky-500/20 text-sky-300 border border-sky-500/30 font-bold' : 'bg-slate-800/50 text-slate-500' }}">
                    2. Dispatched
                </div>
                <div class="p-2 rounded-xl {{ $activeOrder->delivery_status === 'out_for_delivery' ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-500/50 font-bold animate-pulse' : 'bg-slate-800/50 text-slate-500' }}">
                    3. Out for Delivery
                </div>
            </div>

            <!-- OTP & Handover Summary Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-xs">
                <!-- Doorstep OTP Box -->
                <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Doorstep Handover OTP</span>
                        <span class="font-mono text-xl font-black text-amber-400 tracking-widest mt-0.5 block">{{ $otpCode }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 max-w-[100px] text-right">Share with driver upon arrival</span>
                </div>

                <!-- Empty Cans Exchange Info -->
                <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800">
                    <span class="text-[10px] text-slate-400 block font-semibold uppercase">Empty Can Handover</span>
                    @if($activeOrder->has_empty_cans_to_exchange)
                        <span class="font-bold text-cyan-300 block mt-1">1:1 Equal Exchange</span>
                        <span class="text-[11px] text-slate-300 mt-0.5 block">Keep {{ $activeOrder->empty_cans_expected }} empty can(s) ready</span>
                    @else
                        <span class="font-bold text-emerald-400 block mt-1">New Customer (Water Only)</span>
                        <span class="text-[11px] text-slate-300 mt-0.5 block">0 empty cans to hand over</span>
                    @endif
                </div>

                <!-- Driver Contact & Track Action -->
                <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Delivery Schedule</span>
                        <span class="font-bold text-white block mt-0.5">{{ $activeOrder->delivery_date->format('d M') }} ({{ $activeOrder->deliverySlot?->name ?? 'Standard' }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!empty($driverPhone))
                            <a href="tel:{{ $driverPhone }}" class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold" title="Call Driver">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </a>
                        @endif
                        <a href="{{ route('customer.orders.show', $activeOrder->id) }}" class="px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs transition-colors">
                            Track &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- PWA App Launchpad: 4 Quick Action Touch Tiles -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Quick Reorder -->
        <a href="{{ route('home') }}#products" class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm hover:border-brand-500 hover:shadow-md transition-all flex flex-col justify-between">
            <div class="w-10 h-10 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 leading-tight">Order Water</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">20L Cans & Refills</p>
            </div>
        </a>

        <!-- 2. Subscriptions -->
        <a href="{{ route('customer.subscriptions.index') }}" class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm hover:border-brand-500 hover:shadow-md transition-all flex flex-col justify-between">
            <div class="w-10 h-10 rounded-2xl bg-cyan-100 text-cyan-800 flex items-center justify-center font-bold mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 leading-tight">Subscriptions</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Auto Morning Delivery</p>
            </div>
        </a>

        <!-- 3. Empty Can Balance -->
        <a href="{{ route('customer.cans.index') }}" class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm hover:border-brand-500 hover:shadow-md transition-all flex flex-col justify-between">
            <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold mb-3">
                <span class="text-base font-bold">♻</span>
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 leading-tight">Can Passbook</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ (int) (auth()->user()->customerProfile?->canBalances()->sum('held_cans_count') ?? 0) }} Held Bottles</p>
            </div>
        </a>

        <!-- 4. Outstanding Dues -->
        <a href="{{ route('customer.dues.index') }}" class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm hover:border-brand-500 hover:shadow-md transition-all flex flex-col justify-between">
            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 leading-tight">Dues & Ledger</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">₹{{ number_format(auth()->user()->customerProfile?->outstanding_financial_balance ?? 0, 2) }} Due</p>
            </div>
        </a>
    </div>

    <!-- Metric Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Outstanding Financial Due -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Outstanding Dues</p>
                <p class="text-2xl font-black text-slate-900 mt-1">
                    ₹{{ number_format(auth()->user()->customerProfile?->outstanding_financial_balance ?? 0, 2) }}
                </p>
                @if((auth()->user()->customerProfile?->outstanding_financial_balance ?? 0) > 0)
                    <a href="{{ route('customer.dues.index') }}" class="inline-flex items-center gap-1 mt-1 text-[11px] font-bold text-rose-600 hover:text-rose-700 underline">
                        Pay Dues Online &rarr;
                    </a>
                @else
                    <a href="{{ route('customer.ledger.index') }}" class="inline-block mt-1 text-[11px] font-bold text-emerald-600 hover:underline">
                        All Cleared &check; (Passbook)
                    </a>
                @endif
            </div>
            <a href="{{ route('customer.dues.index') }}" class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 hover:bg-rose-100 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </a>
        </div>

        <!-- Empty Can Container Balance -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Containers Held</p>
                <p class="text-2xl font-black text-slate-900 mt-1">
                    {{ (int) (auth()->user()->customerProfile?->canBalances()->sum('held_cans_count') ?? 0) }}
                    <span class="text-xs font-semibold text-slate-500 font-sans">Bottles</span>
                </p>
                <a href="{{ route('customer.cans.index') }}" class="inline-block mt-1 text-[11px] font-bold text-cyan-700 hover:underline">
                    View Can Passbook &rarr;
                </a>
            </div>
            <a href="{{ route('customer.cans.index') }}" class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-700 hover:bg-cyan-100 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </a>
        </div>

        <!-- Pay Later Credit Status -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pay Later Credit</p>
                <p class="text-2xl font-black text-slate-900 mt-1">
                    ₹{{ number_format(auth()->user()->customerProfile?->credit_limit ?? 0, 2) }}
                </p>
                <span class="inline-block mt-1 text-[11px] font-semibold {{ auth()->user()->customerProfile?->allow_pay_later ? 'text-emerald-600' : 'text-slate-500' }}">
                    {{ auth()->user()->customerProfile?->allow_pay_later ? 'Credit Approved' : 'Prepaid Only' }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-700">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Active Deliveries & Recent Orders -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Recent Deliveries & Orders</h3>
                <p class="text-xs text-slate-500">Track current dispatches and view past invoice receipts</p>
            </div>
            <a href="{{ route('customer.orders.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                View All Orders &rarr;
            </a>
        </div>

        @if(isset($recentOrders) && $recentOrders->isNotEmpty())
            <div class="divide-y divide-slate-100 mt-2">
                @foreach($recentOrders as $order)
                    <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-sm text-slate-900">{{ $order->order_number }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $order->order_status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                      ($order->order_status === 'out_for_delivery' ? 'bg-sky-50 text-sky-700 border border-sky-200' :
                                      ($order->order_status === 'delivered' ? 'bg-slate-100 text-slate-700' : 'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                                    {{ str_replace('_', ' ', $order->order_status) }}
                                </span>
                            </div>
                            <p class="text-slate-500">
                                Delivery: <strong class="text-slate-700">{{ $order->delivery_date->format('d M Y') }}</strong> ({{ $order->deliverySlot?->name ?? 'Standard Window' }})
                            </p>
                            <p class="text-slate-600">
                                {{ $order->items->map(fn($i) => "{$i->quantity}x {$i->product_name_snapshot}")->join(', ') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                            <div class="text-left sm:text-right">
                                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Total</span>
                                <span class="text-sm font-black text-slate-900">₹{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition-colors">
                                Track &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="py-12 text-center">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-900">No Orders Placed Yet</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    Ready for crisp, clean drinking water? Select your preferred can capacity and choose a convenient delivery time slot.
                </p>
                <a href="{{ route('home') }}#products" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-500/20">
                    Browse Products
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
