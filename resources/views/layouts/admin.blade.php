@extends('layouts.base')

@section('body')
<div class="min-h-full flex bg-slate-100" x-data="{ sidebarOpen: false, userDropdownOpen: false }">
    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-slate-900 text-slate-300 border-r border-slate-800 z-30">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-6 gap-3 border-b border-slate-800 bg-slate-950">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-500 to-cyan-400 flex items-center justify-center text-white shadow-md">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
            </div>
            <div>
                <span class="text-base font-bold text-white block leading-tight">AquaFlow</span>
                <span class="text-[10px] text-brand-400 font-semibold tracking-wider uppercase block">Operations Hub</span>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto text-xs font-semibold">
            <div class="px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Operations</div>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.orders.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Orders & Dispatches</span>
            </a>
            <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.subscriptions.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Recurring Subscriptions</span>
            </a>
            <a href="{{ route('admin.manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.manifests.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Dispatch Manifests</span>
            </a>
            <a href="{{ route('admin.delivery.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.delivery.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <span>Delivery Slots & Fleet</span>
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Stock & Containers</div>
            <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.products.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Products & Catalog</span>
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.inventory.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Warehouse Inventory</span>
            </a>
            <a href="{{ route('admin.cans.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.cans.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Empty-Can Exchanges</span>
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.suppliers.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Suppliers & Vendors</span>
            </a>
            <a href="{{ route('admin.procurement.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.procurement.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Procurement (POs & GRN)</span>
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Finance & Accounts</div>
            <a href="{{ route('admin.accounts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.accounts.*') || request()->routeIs('admin.payments.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Customer Ledgers & Dues</span>
            </a>
            <a href="{{ route('admin.expenses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.expenses.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                <span>Operating Expenses</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.reports.*') ? 'bg-brand-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Reports & Analytics</span>
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Configuration</div>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-brand-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Customers & Staff</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl {{ request()->routeIs('admin.settings.*') ? 'bg-brand-600 text-white font-bold' : 'hover:bg-slate-800 text-slate-300' }} transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Branch & Settings</span>
            </a>
        </nav>

        <!-- Admin User Bar -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-xs">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="text-xs">
                    <p class="font-bold text-white truncate w-32">{{ auth()->user()->name }}</p>
                    <p class="text-slate-500 text-[10px]">{{ auth()->user()->roles->first()?->name ?? 'Admin' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Admin Wrapper -->
    <div class="flex-1 flex flex-col lg:pl-64">
        <!-- Top App Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-20">
            <!-- Mobile Menu Toggle & Active Branch Display -->
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- Branch Badge -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 hidden sm:inline">Active Branch:</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ session('active_branch_name', 'Central Chennai Hub') }}
                    </span>
                </div>
            </div>

            <!-- Header Right: Quick Status & User Profile -->
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-brand-600 hidden sm:inline-flex items-center gap-1">
                    <span>View Storefront</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>

                <div class="relative" @click.away="userDropdownOpen = false">
                    <button type="button" @click="userDropdownOpen = !userDropdownOpen" class="flex items-center gap-2 text-xs font-semibold text-slate-700 hover:text-slate-900">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-700">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-show="userDropdownOpen" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-50 text-xs">
                        <div class="px-4 py-2 border-b border-slate-100 font-medium text-slate-600">
                            {{ auth()->user()->email ?? auth()->user()->mobile }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 font-semibold">Sign Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @hasSection('page_title')
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">@yield('page_title')</h1>
                    @hasSection('page_subtitle')
                        <p class="text-xs text-slate-500 mt-1">@yield('page_subtitle')</p>
                    @endif
                </div>
                @hasSection('header_actions')
                    <div class="flex items-center gap-3 shrink-0">
                        @yield('header_actions')
                    </div>
                @endif
            </div>
            @endif

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
    </div>
</div>
@endsection
