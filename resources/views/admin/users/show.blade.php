@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('admin.users.index') }}" class="hover:text-brand-600 transition-colors">Users & Access</a>
            <span>/</span>
            <span class="text-slate-800">{{ $user->name }}</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit Account
            </a>

            @if(auth()->id() !== $user->id)
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" onsubmit="return confirm('Toggle status for this user?');">
                    @csrf
                    @if($user->is_active)
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 font-semibold text-xs transition-colors">
                            Deactivate User
                        </button>
                    @else
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-semibold text-xs transition-colors">
                            Activate User
                        </button>
                    @endif
                </form>
            @endif
        </div>
    </div>

    <!-- User Profile Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl {{ $user->user_type === 'admin' ? 'bg-indigo-600' : ($user->user_type === 'staff' ? 'bg-emerald-600' : 'bg-brand-600') }} text-white flex items-center justify-center font-bold text-xl shadow-md">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900">{{ $user->name }}</h1>
                        @if ($user->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                Deactivated
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Member since {{ $user->created_at?->format('M d, Y') ?? 'N/A' }} &bull; ID #{{ $user->id }}
                    </p>
                </div>
            </div>

            <!-- User Category Pill -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-3 py-1.5 rounded-xl border {{ $user->user_type === 'admin' ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : ($user->user_type === 'staff' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-sky-50 border-sky-200 text-sky-700') }} uppercase tracking-wider">
                    {{ ucfirst($user->user_type) }} Account
                </span>
            </div>
        </div>

        <hr class="border-slate-100 my-6">

        <!-- Contact & Security Details -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs">
            <div>
                <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Mobile Phone</p>
                <p class="font-bold text-slate-800 text-sm mt-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    +91 {{ $user->mobile }}
                </p>
            </div>

            <div>
                <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Email Address</p>
                <p class="font-bold text-slate-800 text-sm mt-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    {{ $user->email ?? 'Not configured' }}
                </p>
            </div>

            <div>
                <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Primary Branch Access</p>
                <p class="font-bold text-slate-800 text-sm mt-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    {{ $user->branches->first()?->name ?? 'None Assigned' }}
                </p>
            </div>
        </div>

        <hr class="border-slate-100 my-6">

        <!-- Assigned Roles -->
        <div>
            <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px] mb-2.5">Security Roles & Permissions Assigned</p>
            <div class="flex flex-wrap gap-2">
                @forelse ($user->roles as $role)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                        <svg class="w-3.5 h-3.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ $role->name }}
                    </span>
                @empty
                    <span class="text-xs text-slate-400 italic">No roles directly attached.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Staff Specific Details -->
    @if ($user->staffProfile)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Staff & Logistics Profile
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Designation</p>
                    <p class="text-sm font-bold text-slate-800 mt-1">{{ $user->staffProfile->designation ?? 'Delivery Executive' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Vehicle Number</p>
                    <p class="text-sm font-bold text-slate-800 mt-1">{{ $user->staffProfile->vehicle_number ?? 'Not assigned' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Driving License</p>
                    <p class="text-sm font-bold text-slate-800 mt-1">{{ $user->staffProfile->driving_license_no ?? 'N/A' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Delivery Availability</p>
                    <p class="text-sm font-bold {{ $user->staffProfile->is_available_for_delivery ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                        {{ $user->staffProfile->is_available_for_delivery ? 'Available for Trips' : 'Offline / On Leave' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Customer Specific Details -->
    @if ($user->customerProfile)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Customer Profile & Accounts Overview
                </h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.accounts.customer', $user->id) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                        View Full Ledger &rarr;
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Customer Segment</p>
                    <p class="text-sm font-bold text-slate-800 mt-1 uppercase">{{ $user->customerProfile->customer_type ?? 'Residential' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Financial Dues Balance</p>
                    <p class="text-sm font-bold {{ ($user->customerProfile->outstanding_financial_balance ?? 0) > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1">
                        ₹{{ number_format($user->customerProfile->outstanding_financial_balance ?? 0, 2) }}
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Credit Limit</p>
                    <p class="text-sm font-bold text-slate-800 mt-1">
                        ₹{{ number_format($user->customerProfile->credit_limit ?? 500, 2) }}
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-400 font-semibold">Pay Later Postpaid</p>
                    <p class="text-sm font-bold {{ $user->customerProfile->allow_pay_later ? 'text-emerald-600' : 'text-slate-500' }} mt-1">
                        {{ $user->customerProfile->allow_pay_later ? 'Allowed' : 'Disabled' }}
                    </p>
                </div>
            </div>

            <!-- Saved Addresses -->
            @if ($user->customerProfile->addresses && $user->customerProfile->addresses->count() > 0)
                <div class="pt-4 border-t border-slate-100">
                    <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px] mb-2.5">Saved Delivery Addresses</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        @foreach ($user->customerProfile->addresses as $address)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-slate-800 capitalize">{{ $address->address_label ?? 'Address' }}</span>
                                    @if ($address->is_default)
                                        <span class="text-[9px] font-bold bg-brand-100 text-brand-800 px-2 py-0.5 rounded-full">Default</span>
                                    @endif
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed">
                                    {{ $address->street_address }}, {{ $address->area }}, {{ $address->city }} - {{ $address->pincode }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
