@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header & Batch Generation Panel -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Customer Recurring Subscriptions</h1>
            <p class="text-sm text-slate-500 mt-1">Manage scheduled daily, weekly, and custom automated water deliveries.</p>
        </div>

        <!-- Manual Batch Order Generation Action -->
        <div class="bg-brand-50 border border-brand-200 p-3 rounded-2xl flex flex-col sm:flex-row items-center gap-3">
            <div class="text-xs text-brand-800">
                <span class="font-bold">Dispatch Generation:</span> Generate orders for next cycle
            </div>
            <form action="{{ route('admin.subscriptions.generate-manual') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <input type="date" name="target_date" value="{{ date('Y-m-d', strtotime('+1 day')) }}" class="text-xs rounded-xl border-slate-300 py-1.5 px-2.5 focus:ring-brand-500 focus:border-brand-500">
                <button type="submit" class="px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Generate Orders
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Counter Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Subscriptions</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">All customer plans</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Deliveries</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['active']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Regularly scheduled</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Paused Plans</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ number_format($stats['paused']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Customer holds</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Due Next Cycle</p>
            <p class="text-2xl font-bold text-brand-600 mt-1">{{ number_format($stats['due_tomorrow']) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Scheduled by tomorrow</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search sub # or customer name..." class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ $status === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <select name="frequency" class="w-full text-xs rounded-xl border-slate-300 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">All Frequencies</option>
                    <option value="daily" {{ $frequency === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="alternate_days" {{ $frequency === 'alternate_days' ? 'selected' : '' }}>Alternate Days</option>
                    <option value="weekly" {{ $frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="custom_days" {{ $frequency === 'custom_days' ? 'selected' : '' }}>Custom Days</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.subscriptions.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($subscriptions->isEmpty())
            <div class="p-12 text-center text-slate-500 text-xs">
                No customer subscriptions found matching the filter criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Subscription #</th>
                            <th class="px-6 py-3.5">Customer</th>
                            <th class="px-6 py-3.5">Frequency</th>
                            <th class="px-6 py-3.5">Subscribed Items</th>
                            <th class="px-6 py-3.5">Next Delivery</th>
                            <th class="px-6 py-3.5">Slot</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($subscriptions as $sub)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-slate-900">
                                    <a href="{{ route('admin.subscriptions.show', $sub) }}" class="text-brand-600 hover:underline">
                                        {{ $sub->subscription_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="font-bold text-slate-900">{{ $sub->customer?->user?->name ?? 'Customer' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $sub->customer?->user?->mobile }}</p>
                                </td>
                                <td class="px-6 py-3.5 font-medium text-slate-700">
                                    {{ $sub->frequency_label }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        @foreach($sub->items as $item)
                                            <span class="text-[11px] text-slate-700">
                                                <strong>{{ $item->quantity }}x</strong> {{ $item->product?->name ?? 'Water Can' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    <strong class="text-slate-800">
                                        {{ $sub->next_delivery_date ? $sub->next_delivery_date->format('d M, Y') : 'N/A' }}
                                    </strong>
                                </td>
                                <td class="px-6 py-3.5 text-slate-700">
                                    {{ $sub->deliverySlot?->name ?? 'Standard' }}
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($sub->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    @elseif($sub->status === 'paused')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                            Paused
                                        </span>
                                    @elseif($sub->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                            {{ ucfirst($sub->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-right font-medium">
                                    <a href="{{ route('admin.subscriptions.show', $sub) }}" class="text-brand-600 hover:text-brand-900 font-bold">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($subscriptions->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
