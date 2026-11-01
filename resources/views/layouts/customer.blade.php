@extends('layouts.base')

@section('body')
<div class="min-h-full flex flex-col pb-20 md:pb-8" x-data="{ userMenuOpen: false, cartOpen: false }">
    <!-- Top Customer Header -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo & Branch Pill -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-cyan-400 flex items-center justify-center text-white shadow-sm">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                        </div>
                        <span class="text-lg font-bold tracking-tight text-slate-900 hidden sm:inline">AquaFlow</span>
                    </a>

                    <!-- Branch Indicator -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-brand-50 text-brand-700 border border-brand-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ session('active_branch_name', 'Chennai Hub') }}
                    </span>
                </div>

                <!-- Right Actions: Quick Order CTA & Profile Menu -->
                <div class="flex items-center gap-3">
                    <!-- Cart Link -->
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:text-brand-600 hover:bg-slate-50 transition-colors" title="Shopping Cart">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Cart</span>
                    </a>

                    <!-- Quick Order Button -->
                    <a href="{{ route('home') }}#products" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Order Water
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button type="button" @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="text-xs font-semibold text-slate-700 hidden md:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-slate-400 hidden md:inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="userMenuOpen" x-transition class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-xl border border-slate-100 py-1.5 z-50 text-xs">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-slate-500 truncate">{{ auth()->user()->mobile }}</p>
                            </div>
                            <a href="{{ route('customer.dashboard') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Dashboard</a>
                            <a href="{{ route('customer.subscriptions.index') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">My Subscriptions</a>
                            <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">My Orders</a>
                            <a href="{{ route('customer.dues.index') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Outstanding Dues</a>
                            <a href="{{ route('customer.ledger.index') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Financial Passbook</a>
                            <a href="{{ route('customer.cans.index') }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">Can Exchange Balance</a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 font-medium">Sign Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-32 md:pb-12">
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Sticky Navigation (Fixed 5-Tab Bar) -->
    <nav class="md:hidden mobile-bottom-nav">
        <div class="grid grid-cols-5 h-16">
            <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center justify-center text-xs font-semibold {{ request()->routeIs('customer.dashboard') ? 'text-brand-600' : 'text-slate-500' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Home</span>
            </a>
            <a href="{{ route('home') }}#products" class="flex flex-col items-center justify-center text-xs font-semibold text-slate-500">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Products</span>
            </a>
            <a href="{{ route('home') }}#products" class="flex flex-col items-center justify-center text-xs font-semibold text-brand-600">
                <div class="w-10 h-10 -mt-5 rounded-full bg-brand-600 text-white flex items-center justify-center shadow-lg shadow-brand-500/30">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="mt-0.5">Order</span>
            </a>
            <a href="{{ route('customer.orders.index') }}" class="flex flex-col items-center justify-center text-xs font-semibold {{ request()->routeIs('customer.orders.*') ? 'text-brand-600' : 'text-slate-500' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Orders</span>
            </a>
            <a href="#" class="flex flex-col items-center justify-center text-xs font-semibold text-slate-500">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profile</span>
            </a>
        </div>
    </nav>
</div>
@endsection
