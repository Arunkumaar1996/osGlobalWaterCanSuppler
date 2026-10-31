@extends('layouts.admin')

@section('title', 'Generate Dispatch Manifest - Admin')
@section('page_title', 'Generate Dispatch Manifest & Route Slip')
@section('page_subtitle', 'Bundle staged fulfillments into a departure run with driver and vehicle assignment')

@section('header_actions')
<a href="{{ route('admin.manifests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Manifests</span>
</a>
@endsection

@section('content')
<div class="max-w-5xl">
    <form method="POST" action="{{ route('admin.manifests.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Route & Logistics Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Operating Branch <span class="text-rose-500">*</span></label>
                    <select name="branch_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Departure Warehouse</label>
                    <select name="warehouse_id" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">None / Direct Sourcing</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Vehicle Number</label>
                    <input type="text" name="vehicle_number" placeholder="e.g. TN-04-AB-1234" class="w-full px-4 py-2.5 text-sm font-mono uppercase bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Driver / Delivery Person</label>
                    <input type="text" name="driver_name" placeholder="e.g. Ramesh Kumar" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Driver Phone</label>
                    <input type="text" name="driver_phone" placeholder="e.g. 9876543212" class="w-full px-4 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Departure Run Notes</label>
                <input type="text" name="notes" placeholder="e.g. Morning Sector 1 delivery run; handle with care" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <!-- Fulfillments Selection Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Select Consignments to Bundle</h3>
                    <p class="text-xs text-slate-500">Check all staged order fulfillments that should be loaded onto this truck</p>
                </div>
            </div>

            @error('fulfillments')
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs font-semibold">
                    {{ $message }}
                </div>
            @enderror

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">Select</th>
                            <th class="px-4 py-3">Fulfillment & Order #</th>
                            <th class="px-4 py-3">Destination Area</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Source Facility</th>
                            <th class="px-4 py-3 text-right">Cans Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @forelse($fulfillments as $ful)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3.5 text-center">
                                <input type="checkbox" name="fulfillments[]" value="{{ $ful->id }}" class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-slate-900 block">{{ $ful->fulfillment_number }}</span>
                                <span class="text-slate-400 font-sans text-[11px]">Order: {{ $ful->order?->order_number }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-sans">
                                <span class="font-bold text-slate-800">{{ $ful->order?->snapshot_address['area'] ?? '' }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $ful->order?->snapshot_address['pincode'] ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-sans">
                                <span class="font-semibold text-slate-900">{{ $ful->order?->customer?->user?->name }}</span>
                                <span class="text-[11px] text-slate-500 block font-mono">{{ $ful->order?->customer?->user?->mobile }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-sans text-xs">
                                @if($ful->source_type === 'warehouse')
                                    <span class="font-semibold text-blue-700">{{ $ful->warehouse?->name }}</span>
                                @else
                                    <span class="font-semibold text-purple-700">{{ $ful->supplier?->company_name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-slate-900 text-sm">
                                {{ $ful->total_quantity }} cans
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-sans">
                                No unmanifested fulfillments available. Allocate fulfillments from the Orders Hub first.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.manifests.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700 shadow-sm transition-colors">
                Generate Dispatch Manifest
            </button>
        </div>
    </form>
</div>
@endsection
