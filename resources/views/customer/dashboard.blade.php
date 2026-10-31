@extends('layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner Card -->
    <div class="bg-gradient-to-r from-brand-600 via-brand-700 to-cyan-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-600/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur mb-3">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                Pure Packaged Drinking Water
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome, {{ auth()->user()->name }}!</h1>
            <p class="text-brand-100 text-sm mt-1 max-w-xl">
                Default Address: <span class="font-medium text-white">{{ auth()->user()->customerProfile?->defaultAddress?->formatted_address ?? 'No address set' }}</span>
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <a href="{{ route('home') }}#products" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-white text-brand-700 hover:bg-brand-50 font-bold text-sm shadow-md hover-lift transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Order Water Cans
            </a>
        </div>
    </div>

    <!-- Metric Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Outstanding Financial Due -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Outstanding Dues</p>
                <p class="text-2xl font-black text-slate-900 mt-1">
                    ₹{{ number_format(auth()->user()->customerProfile?->outstanding_financial_balance ?? 0, 2) }}
                </p>
                @if((auth()->user()->customerProfile?->outstanding_financial_balance ?? 0) > 0)
                    <a href="{{ route('customer.dues.index') }}" class="inline-flex items-center gap-1 mt-1 text-[11px] font-bold text-rose-600 hover:text-rose-700 underline">
                        Pay Dues &rarr;
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
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
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
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
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
    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
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
