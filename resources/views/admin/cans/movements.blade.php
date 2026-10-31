@extends('layouts.admin')

@section('title', 'Container Movements Audit Log')

@section('page_title', 'Container Physical Movements Audit Log')

@section('page_actions')
<a href="{{ route('admin.cans.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    &larr; Back to Container Fleet
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.cans.movements') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Movement Type</label>
                <select name="movement_type" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500">
                    <option value="">All Movement Types</option>
                    <option value="delivery_exchange" {{ request('movement_type') === 'delivery_exchange' ? 'selected' : '' }}>Delivery Exchange</option>
                    <option value="return_collection" {{ request('movement_type') === 'return_collection' ? 'selected' : '' }}>Empty Return</option>
                    <option value="missing_penalty" {{ request('movement_type') === 'missing_penalty' ? 'selected' : '' }}>Missing Penalty</option>
                    <option value="audit_reconciliation" {{ request('movement_type') === 'audit_reconciliation' ? 'selected' : '' }}>Audit Reconciliation</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Can Type</label>
                <select name="can_type_id" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500">
                    <option value="">All Can Sizes</option>
                    @foreach($canTypes as $ct)
                        <option value="{{ $ct->id }}" {{ request('can_type_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full py-1.5 px-3 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full py-1.5 px-3 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['movement_type', 'can_type_id', 'date_from', 'date_to']))
                    <a href="{{ route('admin.cans.movements') }}" class="py-2 px-3 text-xs font-bold text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Audit Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Can Type</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Delivered Full (+)</th>
                        <th class="py-3 px-4 text-right">Collected Empty (-)</th>
                        <th class="py-3 px-4 text-right">Missing</th>
                        <th class="py-3 px-4 text-right">Balance</th>
                        <th class="py-3 px-4">Logged By / Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-slate-800">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-sans text-slate-600 whitespace-nowrap">
                                {{ $m->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3 px-4 font-sans">
                                <a href="{{ route('admin.cans.customer', $m->customer_profile_id) }}" class="font-bold text-slate-900 hover:text-brand-600">
                                    {{ $m->customer?->user?->name ?? 'Customer' }}
                                </a>
                                <div class="text-[11px] text-slate-500">{{ $m->customer?->user?->mobile }}</div>
                            </td>
                            <td class="py-3 px-4 font-sans font-bold text-slate-900 whitespace-nowrap">
                                {{ $m->canType?->name ?? 'Can' }}
                            </td>
                            <td class="py-3 px-4 font-sans whitespace-nowrap">
                                @if($m->movement_type === 'delivery_exchange')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-50 text-brand-700 border border-brand-200">
                                        Delivery Exchange
                                    </span>
                                @elseif($m->movement_type === 'return_collection')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Empty Return
                                    </span>
                                @elseif($m->movement_type === 'missing_penalty')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                        Missing Penalty
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        Audit Reconciliation
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $m->cans_delivered_full > 0 ? 'text-brand-600' : 'text-slate-300' }}">
                                {{ $m->cans_delivered_full > 0 ? '+' . $m->cans_delivered_full : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $m->cans_collected_empty > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                                {{ $m->cans_collected_empty > 0 ? '-' . $m->cans_collected_empty : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $m->missing_cans > 0 ? 'text-rose-600' : 'text-slate-300' }}">
                                {{ $m->missing_cans > 0 ? $m->missing_cans : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-black text-slate-900 text-sm">
                                {{ $m->balance_after_movement }}
                            </td>
                            <td class="py-3 px-4 font-sans text-slate-600">
                                <div class="truncate max-w-xs" title="{{ $m->notes }}">{{ $m->notes }}</div>
                                <div class="text-[10px] text-slate-400">By: {{ $m->recordedBy?->name ?? 'System' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs">
                                No container physical movements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection
