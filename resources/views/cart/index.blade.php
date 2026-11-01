@extends('layouts.guest')

@section('title', 'Shopping Cart')

@section('content')
<div class="py-8 sm:py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full">
    <div class="flex items-center justify-between pb-6 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Your Water Cart</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Review your selected can capacities, exchange option, and quantities</p>
        </div>
        <a href="{{ route('home') }}#products" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
            <span>&larr; Add More Water</span>
        </a>
    </div>

    @if($cart->items->isEmpty())
        <!-- Empty Cart State -->
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm mt-8">
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Your Cart is Currently Empty</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                Select from our 20 Litre, 10 Litre, or 5 Litre purified water cans for reliable doorstep delivery.
            </p>
            <a href="{{ route('home') }}#products" class="inline-flex items-center gap-2 mt-6 px-6 py-3 rounded-2xl text-xs font-extrabold text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-500/20 hover-lift transition-all">
                Explore Available Water Cans
            </a>
        </div>
    @else
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Items List -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Cart Items Card Stack -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="divide-y divide-slate-100">
                        @foreach($cart->items as $item)
                            <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center shrink-0">
                                        <svg class="w-8 h-8 text-brand-600" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $item->product->name }}</h3>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-slate-500">₹{{ number_format($item->product->selling_price, 2) }} each</span>
                                            @if($item->product->requires_can_exchange)
                                                <span class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200">
                                                    Exchangeable Bottle
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity Controls & Line Subtotal -->
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                                    <!-- Qty Control -->
                                    <form method="POST" action="{{ route('cart.update', $item->id) }}" class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                                        @csrf
                                        <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}" class="px-3 py-1.5 text-slate-600 hover:bg-slate-200 text-xs font-bold transition-colors">&minus;</button>
                                        <span class="px-3 py-1.5 text-xs font-bold text-slate-800 bg-white min-w-[32px] text-center">{{ $item->quantity }}</span>
                                        <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="px-3 py-1.5 text-slate-600 hover:bg-slate-200 text-xs font-bold transition-colors">&plus;</button>
                                    </form>

                                    <!-- Price -->
                                    <div class="text-right min-w-[80px]">
                                        <span class="text-sm font-black text-slate-900">₹{{ number_format($item->subtotal, 2) }}</span>
                                    </div>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors" title="Remove item">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Empty Water Can Exchange Options for Existing vs New Customers -->
                @if($hasExchanges)
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">♻</span>
                                    Do You Have Empty Water Cans to Exchange?
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Select your bottle exchange status below (total: {{ $exchangeableCount ?? $cart->totalExchangeableCansCount() }} can(s))</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('cart.exchange-mode') }}" id="exchangeModeForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @csrf
                            <!-- Option 1: Existing Customer / Has Empties to Exchange -->
                            <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 {{ $cart->has_empty_cans_to_exchange ? 'border-brand-500 bg-brand-50/50 ring-2 ring-brand-500/20' : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" name="has_empty_cans_to_exchange" value="1" onchange="document.getElementById('exchangeModeForm').submit()" {{ $cart->has_empty_cans_to_exchange ? 'checked' : '' }} class="mt-1 text-brand-600 focus:ring-brand-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 text-sm block">Yes, I have empty cans (1:1 Exchange)</span>
                                    <span class="text-slate-500 block mt-1 leading-normal">
                                        Hand over {{ $exchangeableCount ?? $cart->totalExchangeableCansCount() }} empty can(s) to our delivery driver upon delivery.
                                    </span>
                                    <span class="inline-block mt-2 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        Can Deposit: ₹0 (Refill Water Only)
                                    </span>
                                </div>
                            </label>

                            <!-- Option 2: New Customer / No Empty Cans (Requires Refundable Security Deposit) -->
                            <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-start gap-3 {{ !$cart->has_empty_cans_to_exchange ? 'border-brand-500 bg-brand-50/50 ring-2 ring-brand-500/20' : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" name="has_empty_cans_to_exchange" value="0" onchange="document.getElementById('exchangeModeForm').submit()" {{ !$cart->has_empty_cans_to_exchange ? 'checked' : '' }} class="mt-1 text-brand-600 focus:ring-brand-500">
                                <div class="text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900 text-sm block">No, I'm a new customer</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-100 text-brand-700">New User</span>
                                    </div>
                                    <span class="text-slate-500 block mt-1 leading-normal">
                                        No empty cans? A refundable deposit of ₹150/can is added. 100% refundable when you return cans later.
                                    </span>
                                    <span class="inline-block mt-2 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-100 text-amber-800">
                                        Refundable Deposit: +₹{{ number_format(($exchangeableCount ?? $cart->totalExchangeableCansCount()) * 150, 2) }}
                                    </span>
                                </div>
                            </label>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Right Checkout Summary -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                        Order Summary
                    </h3>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Water Refill Subtotal</span>
                            <span class="font-bold text-slate-900">₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Total Volume</span>
                            <span class="font-semibold text-slate-700">{{ $totalLitres }} Litres</span>
                        </div>

                        <!-- Refundable Can Deposit for New Customers -->
                        @if(($depositAmount ?? 0) > 0)
                            <div class="flex items-center justify-between text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                                <div>
                                    <span class="font-bold block">Can Security Deposit</span>
                                    <span class="text-[10px] text-amber-600">₹150 &times; {{ $exchangeableCount ?? $cart->totalExchangeableCansCount() }} cans (100% Refundable)</span>
                                </div>
                                <span class="font-black text-xs text-amber-900">+₹{{ number_format($depositAmount, 2) }}</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Bottle Deposit</span>
                                <span class="text-emerald-600 font-bold">₹0.00 (1:1 Exchange)</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Estimated Delivery</span>
                            <span class="text-emerald-600 font-bold">Calculated at checkout</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Taxes</span>
                            <span class="text-slate-500 font-medium">Included</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-baseline justify-between">
                        <div>
                            <span class="text-sm font-bold text-slate-900 block">Total Amount</span>
                            @if(($depositAmount ?? 0) > 0)
                                <span class="text-[10px] text-slate-400">Includes refundable bottle deposit</span>
                            @endif
                        </div>
                        <span class="text-2xl font-black text-slate-900">₹{{ number_format($totalPayable ?? ($subtotal + ($depositAmount ?? 0)), 2) }}</span>
                    </div>

                    @auth
                        <a href="{{ route('checkout.index') }}" class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/25 hover-lift transition-all">
                            <span>Proceed to Checkout</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/25 hover-lift transition-all">
                            <span>Register & Checkout</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <p class="text-[11px] text-center text-slate-500">
                            Already have an account? <a href="{{ route('login') }}" class="font-bold text-brand-600">Sign in</a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
