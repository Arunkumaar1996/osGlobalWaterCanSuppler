@extends('layouts.admin')

@section('title', 'Suppliers & Vendors - Admin')
@section('page_title', 'Water Suppliers & Manufacturers')
@section('page_subtitle', 'Manage water can suppliers, purchase agreements, and accounts payable balances')

@section('header_actions')
<a href="{{ route('admin.suppliers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white font-semibold text-sm hover:bg-brand-700 shadow-sm transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span>Register New Supplier</span>
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Search</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Company name, contact, phone..." class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.suppliers.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Supplier / Company</th>
                        <th class="px-6 py-3.5">Contact Person</th>
                        <th class="px-6 py-3.5">Phone & Email</th>
                        <th class="px-6 py-3.5">GST / PAN</th>
                        <th class="px-6 py-3.5">Products Mapped</th>
                        <th class="px-6 py-3.5">Outstanding Payable</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.suppliers.show', $supplier) }}" class="font-bold text-slate-900 hover:text-brand-600 block">
                                {{ $supplier->company_name }}
                            </a>
                            <span class="text-xs text-slate-400">{{ $supplier->city ?? 'Location not specified' }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            {{ $supplier->contact_person }}
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="font-semibold text-slate-800">{{ $supplier->phone }}</div>
                            <div class="text-slate-400">{{ $supplier->email ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 font-mono text-[11px] text-slate-600">
                            <div>{{ $supplier->gst_number ?? 'GST: —' }}</div>
                            <div class="text-slate-400">{{ $supplier->pan_number ?? 'PAN: —' }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-700 font-bold">
                                {{ $supplier->products_count }} Products
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($supplier->outstanding_payable_balance > 0)
                                <div class="font-bold text-rose-600">₹{{ number_format($supplier->outstanding_payable_balance, 2) }}</div>
                                <span class="text-[10px] text-rose-500 font-semibold uppercase">Payable Due</span>
                            @else
                                <div class="font-bold text-emerald-600">₹0.00</div>
                                <span class="text-[10px] text-emerald-500 font-semibold uppercase">Settled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($supplier->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" class="px-3 py-1.5 text-xs font-bold text-brand-600 hover:text-white bg-brand-50 hover:bg-brand-600 rounded-lg transition-colors">
                                    View / Ledger
                                </a>
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" title="Edit Details" class="p-1.5 text-slate-400 hover:text-brand-600 rounded-lg hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            No suppliers registered yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
