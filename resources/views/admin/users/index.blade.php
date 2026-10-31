@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header with Action CTAs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">User Management & Access Control</h1>
            <p class="text-sm text-slate-500 mt-1">Manage customers, delivery executives, operations staff, and administrator privileges.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.permissions') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Permissions Matrix
            </a>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                New User Account
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Accounts</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Platform registered</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Customers</p>
            <p class="text-2xl font-bold text-brand-600 mt-1">{{ number_format($stats['customers']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Residential & B2B</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Delivery Staff</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['staff']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Mobile runsheet drivers</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Administrators</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($stats['admins']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Managers & Accountants</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name, mobile or email..." class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <select name="user_type" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All User Types</option>
                    <option value="customer" {{ $userType === 'customer' ? 'selected' : '' }}>Customers</option>
                    <option value="staff" {{ $userType === 'staff' ? 'selected' : '' }}>Staff / Drivers</option>
                    <option value="admin" {{ $userType === 'admin' ? 'selected' : '' }}>Administrators</option>
                </select>
            </div>
            <div>
                <select name="role_id" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ $roleId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="branch_id" class="w-full text-xs rounded-xl border-slate-300 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($users->isEmpty())
            <div class="p-12 text-center text-slate-500 text-xs">
                No user accounts found matching the filter criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">User Type</th>
                            <th class="px-6 py-3.5">Assigned Roles</th>
                            <th class="px-6 py-3.5">Branch Hub</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.users.show', $u) }}" class="font-bold text-slate-900 hover:text-brand-600">
                                                {{ $u->name }}
                                            </a>
                                            <p class="text-[11px] text-slate-400">{{ $u->mobile }} {{ $u->email ? '• ' . $u->email : '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($u->user_type === 'admin')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                            Admin
                                        </span>
                                    @elseif($u->user_type === 'staff')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            Delivery Staff
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold bg-brand-100 text-brand-800">
                                            Customer
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($u->roles as $r)
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                                {{ $r->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-slate-700">
                                    {{ $u->branches->first()?->name ?? 'General' }}
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($u->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-right font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $u) }}" class="text-slate-600 hover:text-brand-600 font-bold">
                                            View
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('admin.users.edit', $u) }}" class="text-brand-600 hover:text-brand-800 font-bold">
                                            Edit
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold {{ $u->is_active ? 'text-rose-600 hover:text-rose-800' : 'text-emerald-600 hover:text-emerald-800' }}" onclick="return confirm('Change status for {{ $u->name }}?');">
                                                {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
