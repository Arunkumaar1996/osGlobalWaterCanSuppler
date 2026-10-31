@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('admin.users.index') }}" class="hover:text-brand-600 transition-colors">Users & Access</a>
            <span>/</span>
            <a href="{{ route('admin.users.show', $user) }}" class="hover:text-brand-600 transition-colors">{{ $user->name }}</a>
            <span>/</span>
            <span class="text-slate-800">Edit Account</span>
        </div>
        <a href="{{ route('admin.users.show', $user) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            &larr; View Account
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ userType: '{{ old('user_type', $user->user_type) }}' }">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h1 class="text-xl font-bold text-slate-900">Modify User: {{ $user->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Update profile information, roles, branch affiliation, or reset password.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <p class="font-bold">Please correct the following errors:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Identity & Credentials -->
            <div>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">1. Identity & Credentials</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mobile Phone (10 digits) *</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" required maxlength="10"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">New Password (Leave blank to keep existing)</label>
                        <input type="password" name="password" minlength="6" placeholder="Enter only to change"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Account Status *</label>
                        <select name="is_active" required class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                            <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Active (Enabled)</option>
                            <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Deactivated (Suspended)</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- 2. Role, User Type & Branch -->
            <div>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">2. Role, User Type & Branch</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">User Category *</label>
                        <select name="user_type" x-model="userType" required
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                            <option value="staff" {{ old('user_type', $user->user_type) === 'staff' ? 'selected' : '' }}>Staff / Operations Member</option>
                            <option value="admin" {{ old('user_type', $user->user_type) === 'admin' ? 'selected' : '' }}>Platform Administrator</option>
                            <option value="customer" {{ old('user_type', $user->user_type) === 'customer' ? 'selected' : '' }}>Customer (Portal Access)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assigned Primary Branch *</label>
                        <select name="branch_id" required
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                            @php
                                $selectedBranchId = old('branch_id', $user->branches->first()?->id ?? ($branches->first()?->id));
                            @endphp
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Access Roles & Permission Profiles *</label>
                        @php
                            $userRoleIds = old('role_ids', $user->roles->pluck('id')->toArray());
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            @foreach ($roles as $role)
                                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 hover:border-brand-400 cursor-pointer transition-colors bg-white hover:bg-brand-50/20">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                        {{ in_array($role->id, $userRoleIds) ? 'checked' : '' }}
                                        class="mt-0.5 rounded text-brand-600 focus:ring-brand-500">
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">{{ $role->name }}</p>
                                        <p class="text-[10px] text-slate-400 leading-tight mt-0.5">{{ $role->description ?? 'Standard role capabilities' }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Staff Logistics Profile (Conditional) -->
            <div x-show="userType === 'staff'" x-transition class="space-y-4">
                <hr class="border-slate-100">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">3. Delivery & Fleet Settings</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Designation</label>
                        <input type="text" name="designation" value="{{ old('designation', $user->staffProfile?->designation) }}"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assigned Vehicle Number</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $user->staffProfile?->vehicle_number) }}"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
            </div>

            <!-- 4. Customer Financial Settings (Conditional) -->
            <div x-show="userType === 'customer'" x-transition class="space-y-4">
                <hr class="border-slate-100">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">4. Customer Financial Terms</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Credit Limit (₹)</label>
                        <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $user->customerProfile?->credit_limit ?? 500) }}"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Postpaid / Pay Later Policy</label>
                        <select name="allow_pay_later" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                            <option value="0" {{ !old('allow_pay_later', $user->customerProfile?->allow_pay_later) ? 'selected' : '' }}>Disable Pay Later (Prepaid Only)</option>
                            <option value="1" {{ old('allow_pay_later', $user->customerProfile?->allow_pay_later) ? 'selected' : '' }}>Enable Pay Later (Invoice on Delivery)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.users.show', $user) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs shadow-sm transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
