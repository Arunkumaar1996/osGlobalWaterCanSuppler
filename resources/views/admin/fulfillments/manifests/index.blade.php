@extends('layouts.admin')

@section('title', 'Dispatch Manifests - Admin')
@section('page_title', 'Dispatch Manifests & Packing Slips')
@section('page_subtitle', 'Batch driver departure lists, route packing slips, and loading manifests')

@section('header_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Orders Hub</span>
    </a>
    <a href="{{ route('admin.manifests.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white font-semibold text-sm hover:bg-brand-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Generate New Manifest</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Manifest Number</th>
                        <th class="px-6 py-3.5">Warehouse / Facility</th>
                        <th class="px-6 py-3.5">Vehicle & Driver</th>
                        <th class="px-6 py-3.5">Consignments Bundled</th>
                        <th class="px-6 py-3.5">Generated Date</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @forelse($manifests as $man)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.manifests.show', $man) }}" class="font-bold text-brand-600 hover:text-brand-700 block">
                                {{ $man->manifest_number }}
                            </a>
                            <span class="text-[11px] text-slate-400 font-sans">By: {{ $man->generatedBy?->name ?? 'Admin' }}</span>
                        </td>
                        <td class="px-6 py-4 font-sans text-xs">
                            <span class="font-semibold text-slate-800 block">{{ $man->warehouse?->name ?? 'Central Branch' }}</span>
                            <span class="text-slate-400">{{ $man->branch?->name }}</span>
                        </td>
                        <td class="px-6 py-4 font-sans text-xs">
                            <span class="font-bold text-slate-900 block">{{ $man->vehicle_number ?? 'Vehicle unassigned' }}</span>
                            <span class="text-slate-500">{{ $man->driver_name ?? 'Driver unassigned' }} {{ $man->driver_phone ? '(' . $man->driver_phone . ')' : '' }}</span>
                        </td>
                        <td class="px-6 py-4 font-sans font-bold text-slate-900">
                            {{ $man->fulfillments->count() }} Orders
                        </td>
                        <td class="px-6 py-4 text-slate-700 font-sans text-xs">
                            {{ $man->generated_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4 font-sans">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ ucfirst($man->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-sans">
                            <a href="{{ route('admin.manifests.show', $man) }}" class="px-3 py-1.5 text-xs font-bold text-slate-700 hover:text-white bg-slate-100 hover:bg-slate-900 rounded-lg transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Packing Slip</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-sans">
                            No dispatch manifests generated yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($manifests->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $manifests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
