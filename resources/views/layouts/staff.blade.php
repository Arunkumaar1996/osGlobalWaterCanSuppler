@extends('layouts.base')

@section('body')
<div class="min-h-full flex flex-col bg-slate-900 text-slate-100 pb-20">
    <!-- Staff Top Header -->
    <header class="sticky top-0 z-40 bg-slate-950 border-b border-slate-800 px-4 py-3">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-sm shadow-md">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white leading-tight">{{ auth()->user()->name }}</h1>
                    <p class="text-[11px] text-slate-400">
                        {{ auth()->user()->staffProfile?->vehicle_number ?? 'Vehicle Assigned' }}
                    </p>
                </div>
            </div>

            <!-- Cash In Hand Live Counter -->
            <div class="text-right">
                <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-semibold">Cash in Hand</span>
                <span class="text-sm font-extrabold text-emerald-400">
                    ₹{{ number_format(auth()->user()->staffProfile?->current_cash_in_hand ?? 0, 2) }}
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content for Delivery Staff -->
    <main class="flex-grow max-w-md w-full mx-auto px-4 py-4">
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-950 border border-emerald-700 text-emerald-200 rounded-xl text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-950 border border-rose-700 text-rose-200 rounded-xl text-xs font-semibold">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Staff Mobile Bottom Sticky Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-950 border-t border-slate-800 z-50">
        <div class="max-w-md mx-auto grid grid-cols-3 h-16">
            <a href="{{ route('staff.deliveries') }}" class="flex flex-col items-center justify-center text-xs font-bold {{ request()->routeIs('staff.deliveries') ? 'text-brand-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Deliveries</span>
            </a>
            <a href="{{ route('staff.cash-log') }}" class="flex flex-col items-center justify-center text-xs font-bold {{ request()->routeIs('staff.cash-log') ? 'text-brand-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Cash Log</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-center justify-center">
                @csrf
                <button type="submit" class="flex flex-col items-center justify-center text-xs font-bold text-rose-400">
                    <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Exit</span>
                </button>
            </form>
        </div>
    </nav>
</div>
@endsection
