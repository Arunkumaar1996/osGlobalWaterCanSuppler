@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('admin.users.index') }}" class="hover:text-brand-600 transition-colors">Users & Access</a>
            <span>/</span>
            <span class="text-slate-800">Create Account</span>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            &larr; Back to Directory
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ userType: '{{ old('user_type', 'staff') }}' }">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h1 class="text-xl font-bold text-slate-900">Provision New User Account</h1>
            <p class="text-xs text-slate-500 mt-1">Create credentials, assign operational roles, and grant branch access.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-6">
            @csrf

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

            <!-- 1. Basic Account Information -->
            <div>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">1. Identity & Credentials</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Ramesh Kumar"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500 @error('name') border-rose-400 @enderror">
                        @error('name') <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mobile Phone (10 digits) *</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" required maxlength="10" placeholder="e.g. 9876543210"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500 @error('mobile') border-rose-400 @enderror">
                        @error('mobile') <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address (Optional for staff/customers)</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. ramesh@aquaflow.com"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500 @error('email') border-rose-400 @enderror">
                        @error('email') <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="Minimum 6 characters"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500 @error('password') border-rose-400 @enderror">
                        @error('password') <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- 2. Role & Branch Assignment -->
            <div>
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">2. Role, User Type & Branch</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">User Category *</label>
                        <select name="user_type" x-model="userType" required
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                            <option value="staff">Staff / Operations Member</option>
                            <option value="admin">Platform Administrator</option>
                            <option value="customer">Customer (Direct Portal Access)</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Staff accounts can access delivery runsheets or warehouse scanning.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assigned Primary Branch *</label>
                        <select name="branch_id" required
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Access Roles & Permission Profiles *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            @foreach ($roles as $role)
                                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 hover:border-brand-400 cursor-pointer transition-colors bg-white hover:bg-brand-50/20">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                        {{ (is_array(old('role_ids')) && in_array($role->id, old('role_ids'))) || ($loop->first && !old('role_ids')) ? 'checked' : '' }}
                                        class="mt-0.5 rounded text-brand-600 focus:ring-brand-500">
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">{{ $role->name }}</p>
                                        <p class="text-[10px] text-slate-400 leading-tight mt-0.5">{{ $role->description ?? 'Standard role capabilities' }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('role_ids') <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- 3. Staff Logistics Profile (Conditional) -->
            <div x-show="userType === 'staff'" x-transition class="space-y-4">
                <hr class="border-slate-100">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">3. Delivery & Fleet Profile</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Designation</label>
                        <input type="text" name="designation" value="{{ old('designation', 'Delivery Executive') }}" placeholder="e.g. Delivery Executive, Inventory Supervisor"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assigned Vehicle Number</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="e.g. TN-09-AB-1234"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Driving License No.</label>
                        <input type="text" name="driving_license_no" value="{{ old('driving_license_no') }}" placeholder="e.g. DL-0420110012345"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="e.g. 9840012345"
                            class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs shadow-sm transition-colors">
                    Create User Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
