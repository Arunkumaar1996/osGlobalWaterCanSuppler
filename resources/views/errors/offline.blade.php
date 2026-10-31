@extends('layouts.base')

@section('body')
<div class="min-h-full flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full text-center space-y-6">
        <!-- Brand & Offline Icon -->
        <div class="relative inline-flex items-center justify-center">
            <div class="w-24 h-24 rounded-3xl bg-brand-50 border-2 border-brand-200 flex items-center justify-center text-brand-600 shadow-sm">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-12.728 0m0 0l2.829-2.829m-2.829 2.829L3 21m2.828-15.536a9 9 0 0112.728 0M3 3l18 18"/>
                </svg>
            </div>
            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500"></span>
            </span>
        </div>

        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">You're Currently Offline</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-sm mx-auto">
                AquaFlow requires an active internet connection to process orders, balance adjustments, and doorstep deliveries.
            </p>
        </div>

        <!-- Connection Checklist -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm text-left text-xs text-slate-600 space-y-2.5">
            <p class="font-bold text-slate-800 text-[11px] uppercase tracking-wider mb-2">Check Connection Steps:</p>
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-[10px]">1</span>
                <span>Check if mobile data or Wi-Fi is turned on.</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-[10px]">2</span>
                <span>Ensure Airplane Mode is disabled.</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-[10px]">3</span>
                <span>Click the retry button below once your connection is back.</span>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button type="button" onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4 animate-spin-reverse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Retry Connection
            </button>
            <a href="tel:+919876543210" class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-sm transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Call Support
            </a>
        </div>

        <p class="text-[11px] text-slate-400">
            AquaFlow PWA Cache v1.2 &bull; Emergency Dispatch: 1800-425-AQUA
        </p>
    </div>
</div>
@endsection
