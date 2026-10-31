@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">System & Branch Settings</h1>
            <p class="text-sm text-slate-500 mt-1">Configure distribution hubs, financial terms, container deposit policies, and server utilities.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Active Hub: {{ $branch->name }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <p class="font-bold">Errors encountered:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Branch Details & Policies -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 1. Hub / Primary Branch Configuration Form -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Hub & Fulfillment Center Profile</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Physical address and contact parameters displayed on dispatch slips and customer bills.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wider">
                        {{ $branch->code }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.settings.branch.update', $branch) }}" class="p-6 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Hub / Branch Name *</label>
                            <input type="text" name="name" value="{{ old('name', $branch->name) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Branch Code *</label>
                            <input type="text" name="code" value="{{ old('code', $branch->code) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Dispatch Phone *</label>
                            <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Official Operations Email *</label>
                            <input type="email" name="email" value="{{ old('email', $branch->email) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Facility Street Address *</label>
                            <input type="text" name="address" value="{{ old('address', $branch->address) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">City *</label>
                            <input type="text" name="city" value="{{ old('city', $branch->city) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">State *</label>
                            <input type="text" name="state" value="{{ old('state', $branch->state) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pincode *</label>
                            <input type="text" name="pincode" value="{{ old('pincode', $branch->pincode) }}" required
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs shadow-sm transition-colors">
                            Update Branch Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2. Container & Deposit Policy Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Container Security & Empty Can Exchange Policy</h2>
                        <p class="text-xs text-slate-500">Strict separation between physical container balance and customer financial balance.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Default 20L Can Deposit</p>
                        <p class="text-base font-bold text-slate-900 mt-1">₹150.00 / can</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Refundable on return</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Lost / Damaged Penalty</p>
                        <p class="text-base font-bold text-rose-600 mt-1">₹150.00 / can</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Debited to customer ledger</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-slate-400 font-semibold text-[10px] uppercase">Reconciliation Cycle</p>
                        <p class="text-base font-bold text-slate-900 mt-1">30 Days Max</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Automated overdue alerts</p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Multi-Ledger Integrity Standard
                    </p>
                    <p class="text-[11px] text-blue-800 mt-1 leading-relaxed">
                        Customer financial dues (<code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">customer_ledger</code>) and physical container return debits (<code class="bg-blue-100 px-1 py-0.5 rounded text-blue-900">can_movements</code>) are permanently segregated. Penalties are only converted to financial debits with an explicit audit trail.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Col: System Utilities, Diagnostics & Server Controls -->
        <div class="space-y-6">
            <!-- Platform Diagnostics Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    Platform Diagnostics
                </h2>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-medium">PHP Environment</span>
                        <span class="font-bold text-slate-800 font-mono">v{{ $phpVersion }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-medium">Laravel Framework</span>
                        <span class="font-bold text-slate-800 font-mono">v{{ $laravelVersion }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-medium">Public Storage Symlink</span>
                        @if ($storageLinked)
                            <span class="inline-flex items-center gap-1 font-bold text-emerald-600">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Linked & Verified
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 font-bold text-rose-600">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                Missing Symlink
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 font-medium">Shared Hosting Target</span>
                        <span class="font-bold text-emerald-700">100% Zero Node.js</span>
                    </div>
                </div>
            </div>

            <!-- Zero-SSH System Maintenance Console -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Shared Hosting Maintenance
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Execute administrative commands without SSH or cPanel terminal.</p>
                </div>

                <div class="space-y-3">
                    <!-- Storage Link Trigger -->
                    <form method="POST" action="{{ route('admin.settings.storage-link') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-brand-500 text-left transition-colors group">
                            <div>
                                <p class="text-xs font-bold text-slate-800 group-hover:text-brand-600">Rebuild Storage Symlink</p>
                                <p class="text-[10px] text-slate-400">Links storage/app/public to public/storage</p>
                            </div>
                            <span class="text-xs text-brand-600 font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </button>
                    </form>

                    <!-- Cache Clear Trigger -->
                    <form method="POST" action="{{ route('admin.settings.optimize-clear') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-brand-500 text-left transition-colors group">
                            <div>
                                <p class="text-xs font-bold text-slate-800 group-hover:text-brand-600">Clear All Caches (Optimize Clear)</p>
                                <p class="text-[10px] text-slate-400">Clears route, view, config and app cache</p>
                            </div>
                            <span class="text-xs text-brand-600 font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
                <h2 class="text-sm font-bold text-slate-900">Security & Roles</h2>
                <a href="{{ route('admin.users.permissions') }}" class="flex items-center justify-between text-xs text-brand-600 font-semibold hover:text-brand-700">
                    <span>Manage Roles & Permissions Matrix</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between text-xs text-brand-600 font-semibold hover:text-brand-700">
                    <span>Manage User Accounts & Staff</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
