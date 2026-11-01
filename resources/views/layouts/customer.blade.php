@extends('layouts.base')

@section('body')
<div class="min-h-full flex flex-col pb-24 md:pb-8" x-data="{
    userMenuOpen: false,
    accountDrawerOpen: false,
    iosInstallModal: false,
    pwaCanInstall: false,
    pwaInstalled: false,
    pwaBannerDismissed: localStorage.getItem('aquaflow_pwa_dismissed') === '1',
    init() {
        if (window.isPwaStandalone) {
            this.pwaInstalled = true;
        }
        window.addEventListener('pwa-installable', () => {
            this.pwaCanInstall = true;
        });
        window.addEventListener('pwa-installed', () => {
            this.pwaInstalled = true;
            this.pwaCanInstall = false;
        });
    },
    installApp() {
        if (window.isIosDevice && window.isIosDevice() && !window.isPwaStandalone) {
            this.iosInstallModal = true;
            return;
        }
        if (window.triggerPwaInstall) {
            window.triggerPwaInstall();
        } else {
            this.iosInstallModal = true;
        }
    },
    dismissPwaBanner() {
        this.pwaBannerDismissed = true;
        localStorage.setItem('aquaflow_pwa_dismissed', '1');
    }
}">
    <!-- PWA Smart Install Banner (Dismissable & Interactive) -->
    <div x-show="!pwaInstalled && !pwaBannerDismissed" x-cloak class="bg-gradient-to-r from-sky-600 via-brand-600 to-cyan-600 text-white px-4 py-2.5 shadow-md flex items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5 overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 fill-current text-white" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
            </div>
            <div class="truncate">
                <p class="font-bold leading-tight truncate">Install AquaFlow Water App</p>
                <p class="text-[11px] text-sky-100 truncate hidden sm:block">Faster 1-tap reordering, doorstep live OTP tracking & offline passbook</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" @click="installApp()" class="px-3 py-1.5 rounded-lg bg-white text-brand-700 hover:bg-sky-50 font-bold text-xs shadow-sm transition-colors">
                Install App
            </button>
            <button type="button" @click="dismissPwaBanner()" class="p-1 text-white/80 hover:text-white text-base leading-none" title="Dismiss">&times;</button>
        </div>
    </div>

    <!-- Top Customer App Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo & Branch Pill -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-cyan-400 flex items-center justify-center text-white shadow-sm">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-slate-900 block leading-none">AquaFlow</span>
                            <span class="text-[10px] font-semibold text-brand-600 block sm:hidden">Customer App</span>
                        </div>
                    </a>

                    <!-- Branch Indicator -->
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-brand-50 text-brand-700 border border-brand-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ session('active_branch_name', 'Chennai Hub') }}
                    </span>
                </div>

                <!-- Right Actions: PWA Install Pill, Cart & Profile -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Quick Install App Pill (shown if browser allows installation) -->
                    <button type="button" @click="installApp()" x-show="!pwaInstalled" x-cloak
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 text-xs font-bold transition-all shadow-xs">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span class="hidden sm:inline">Install App</span>
                        <span class="sm:hidden">App</span>
                    </button>

                    <!-- Cart Link with Real-time Count Badge -->
                    @php
                        $cartItemCount = \App\Models\Cart::where('user_id', auth()->id())->first()?->items()->sum('quantity') ?? 0;
                    @endphp
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 text-slate-700 hover:text-brand-600 hover:bg-slate-50 transition-colors" title="Shopping Cart">
                        <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @if($cartItemCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-brand-600 text-white text-[10px] font-black flex items-center justify-center shadow-sm">
                                {{ $cartItemCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Quick Order Button (Desktop) -->
                    <a href="{{ route('home') }}#products" class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Order Water
                    </a>

                    <!-- Profile Dropdown & Mobile Drawer Trigger -->
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button type="button" @click="accountDrawerOpen = true" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-600 to-cyan-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="text-xs font-semibold text-slate-700 hidden md:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-slate-400 hidden md:inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Desktop Quick Menu -->
                        <div x-show="userMenuOpen" x-transition class="hidden md:block absolute right-0 mt-2 w-52 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-50 text-xs">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-slate-500 truncate text-[11px]">{{ auth()->user()->mobile }}</p>
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
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-5 pb-28 md:pb-12">
        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-base font-bold">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs sm:text-sm font-semibold shadow-xs">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Slide-Over Account Drawer (PWA Native App Feel) -->
    <div x-show="accountDrawerOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="accountDrawerOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-xs sm:max-w-sm bg-white shadow-2xl flex flex-col justify-between">
                <div>
                    <!-- Drawer Header -->
                    <div class="p-5 bg-gradient-to-r from-brand-600 to-cyan-600 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur flex items-center justify-center font-bold text-sm text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm leading-tight">{{ auth()->user()->name }}</h3>
                                <p class="text-[11px] text-sky-100">{{ auth()->user()->mobile }}</p>
                            </div>
                        </div>
                        <button type="button" @click="accountDrawerOpen = false" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
                    </div>

                    <!-- User Account Quick Stats -->
                    <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Account Type</span>
                            <span class="font-bold text-slate-800 uppercase">{{ auth()->user()->customerProfile?->customer_type ?? 'Residential' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Hub Branch</span>
                            <span class="font-bold text-brand-600">{{ session('active_branch_name', 'Chennai Hub') }}</span>
                        </div>
                    </div>

                    <!-- Navigation Items List -->
                    <nav class="p-3 space-y-1 text-xs">
                        <p class="px-3 pt-2 pb-1 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Water & Orders</p>
                        <a href="{{ route('customer.dashboard') }}" @click="accountDrawerOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard Home</span>
                        </a>
                        <a href="{{ route('home') }}#products" @click="accountDrawerOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Order Water Cans</span>
                        </a>
                        <a href="{{ route('customer.orders.index') }}" @click="accountDrawerOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Order History & Invoices</span>
                        </a>
                        <a href="{{ route('customer.subscriptions.index') }}" @click="accountDrawerOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Recurring Subscriptions</span>
                        </a>

                        <p class="px-3 pt-3 pb-1 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Passbook & Ledger</p>
                        <a href="{{ route('customer.cans.index') }}" @click="accountDrawerOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <span class="w-4 h-4 text-brand-600 flex items-center justify-center font-bold">♻</span>
                            <span>Empty Can Holding Passbook</span>
                        </a>
                        <a href="{{ route('customer.dues.index') }}" @click="accountDrawerOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Outstanding Dues (Pay Online)</span>
                        </a>
                        <a href="{{ route('customer.ledger.index') }}" @click="accountDrawerOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Financial Passbook Statement</span>
                        </a>

                        <!-- Install PWA Action in Drawer -->
                        <div class="pt-2" x-show="!pwaInstalled">
                            <button type="button" @click="installApp(); accountDrawerOpen = false;" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors">
                                <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Install AquaFlow App</span>
                            </button>
                        </div>
                    </nav>
                </div>

                <!-- Drawer Footer (Sign Out) -->
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs text-rose-600 bg-rose-50 hover:bg-rose-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Sign Out of Account</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- iOS Safari "Add to Home Screen" Instructions Modal -->
    <div x-show="iosInstallModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-end sm:items-center justify-center p-4">
        <div @click.away="iosInstallModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-slate-800 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Install on iPhone / iPad</h3>
                </div>
                <button type="button" @click="iosInstallModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Add AquaFlow to your iOS Home Screen for instant 1-tap reordering and full-screen app experience:
            </p>

            <div class="space-y-3 text-xs">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0">1</span>
                    <p class="text-slate-700">Tap the <strong class="text-slate-900">Share</strong> icon <span class="px-1.5 py-0.5 rounded bg-white border border-slate-200 font-bold">⎋</span> at bottom of Safari</p>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0">2</span>
                    <p class="text-slate-700">Scroll down and tap <strong class="text-slate-900">'Add to Home Screen'</strong> <span class="px-1.5 py-0.5 rounded bg-white border border-slate-200 font-bold">➕</span></p>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0">3</span>
                    <p class="text-slate-700">Tap <strong class="text-slate-900">'Add'</strong> in the top right corner</p>
                </div>
            </div>

            <button type="button" @click="iosInstallModal = false" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition-colors">
                Got It
            </button>
        </div>
    </div>

    <!-- Mobile Bottom Sticky Navigation (Fixed 5-Tab App Bar) -->
    <nav class="md:hidden mobile-bottom-nav">
        <div class="grid grid-cols-5 h-16 items-center">
            <!-- Tab 1: Home -->
            <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center justify-center text-[10px] font-bold transition-colors {{ request()->routeIs('customer.dashboard') ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Home</span>
            </a>

            <!-- Tab 2: Subscriptions -->
            <a href="{{ route('customer.subscriptions.index') }}" class="flex flex-col items-center justify-center text-[10px] font-bold transition-colors {{ request()->routeIs('customer.subscriptions.*') ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Subscribe</span>
            </a>

            <!-- Tab 3: Quick Order Center FAB -->
            <a href="{{ route('home') }}#products" class="flex flex-col items-center justify-center text-[10px] font-bold text-brand-600">
                <div class="w-11 h-11 -mt-6 rounded-full bg-gradient-to-tr from-brand-600 to-cyan-500 text-white flex items-center justify-center shadow-lg shadow-brand-500/40 transform active:scale-95 transition-all">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="mt-0.5 text-slate-800 font-extrabold">Order</span>
            </a>

            <!-- Tab 4: Orders -->
            <a href="{{ route('customer.orders.index') }}" class="flex flex-col items-center justify-center text-[10px] font-bold transition-colors {{ request()->routeIs('customer.orders.*') ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Orders</span>
            </a>

            <!-- Tab 5: Account (Opens PWA Mobile Drawer) -->
            <button type="button" @click="accountDrawerOpen = true" class="flex flex-col items-center justify-center text-[10px] font-bold transition-colors text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Account</span>
            </button>
        </div>
    </nav>
</div>
@endsection
