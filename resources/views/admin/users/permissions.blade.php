@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:text-brand-600 transition-colors">Users & Access</a>
                <span>/</span>
                <span class="text-slate-800">Permissions Matrix</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Role-Based Access Control (RBAC) Matrix</h1>
            <p class="text-sm text-slate-500 mt-1">Platform permission capabilities and authorization mapping across enterprise operational roles.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                &larr; Back to Users
            </a>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs shadow-sm transition-colors">
                New User
            </a>
        </div>
    </div>

    <!-- Role Cards Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($roles as $role)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                            Role #{{ $role->id }}
                        </span>
                        <span class="text-xs font-bold text-slate-500">
                            {{ $role->permissions->count() }} Perms
                        </span>
                    </div>
                    <h2 class="text-base font-bold text-slate-900 mt-2">{{ $role->name }}</h2>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $role->description ?? 'Platform administrative role profile.' }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span>Guard: web</span>
                    <a href="{{ route('admin.users.index', ['role_id' => $role->id]) }}" class="font-semibold text-brand-600 hover:text-brand-700">
                        Filter Users &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Detailed Permissions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-bold text-slate-900">Enterprise Permission Entitlements by Module</h2>
            <p class="text-xs text-slate-500 mt-1">Granular capabilities granted to each operational role in the AquaFlow engine.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider text-[10px] font-bold">
                        <th class="py-3 px-4 w-64">Permission & Slug</th>
                        <th class="py-3 px-3">Module</th>
                        @foreach($roles as $role)
                            <th class="py-3 px-2 text-center whitespace-nowrap">{{ $role->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $allPermissions = \App\Models\Permission::orderBy('module')->orderBy('name')->get();
                    @endphp
                    @forelse($allPermissions as $perm)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                <div>{{ $perm->name }}</div>
                                <code class="text-[10px] text-slate-400 font-mono">{{ $perm->slug }}</code>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                    {{ $perm->module ?? 'general' }}
                                </span>
                            </td>
                            @foreach($roles as $role)
                                @php
                                    $hasPerm = $role->permissions->contains('id', $perm->id) || $role->name === 'Super Admin';
                                @endphp
                                <td class="py-3 px-2 text-center">
                                    @if($hasPerm)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold" title="Allowed">
                                            &check;
                                        </span>
                                    @else
                                        <span class="text-slate-300 font-mono text-sm">&minus;</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + $roles->count() }}" class="py-6 text-center text-slate-400">
                                No permissions registered in database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
