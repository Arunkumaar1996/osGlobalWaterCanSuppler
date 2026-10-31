@extends('layouts.customer')

@section('content')
<div class="space-y-6">
    <!-- Header with Breadcrumbs & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.dashboard') }}" class="hover:text-brand-600">Home</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Subscriptions</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Recurring Water Subscriptions</h1>
            <p class="text-sm text-slate-500 mt-1">Set up automated daily, alternate days, or weekly water can delivery without re-ordering every time.</p>
        </div>

        <a href="{{ route('customer.subscriptions.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create New Subscription
        </a>
    </div>

    <!-- Metric Counter Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Plans</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $activeCount }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Delivering regularly</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Paused Plans</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ $pausedCount }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Temporarily on hold</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Plans</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $subscriptions->total() }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Configured lifetime</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Hassle-Free</p>
            <p class="text-xs font-semibold text-brand-600 mt-2">Zero Reorder Effort</p>
            <p class="text-xs text-slate-400 mt-0.5">Auto-dispatched daily</p>
        </div>
    </div>

    <!-- Subscriptions List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Your Subscription Schedules</h2>
            <span class="text-xs font-medium text-slate-500">{{ $subscriptions->total() }} plans found</span>
        </div>

        @if($subscriptions->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-50 flex items-center justify-center text-brand-600">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No active water subscriptions yet</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Set up your delivery frequency once and our delivery team will deliver fresh water cans right to your door automatically.</p>
                <a href="{{ route('customer.subscriptions.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl text-xs shadow-sm">
                    Set Up First Subscription
                </a>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($subscriptions as $sub)
                    <div class="p-6 hover:bg-slate-50 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2 flex-grow">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="font-bold text-slate-900 text-base">#{{ $sub->subscription_number }}</span>
                                
                                @if($sub->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active
                                    </span>
                                @elseif($sub->status === 'paused')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Paused
                                    </span>
                                @elseif($sub->status === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-1 text-xs font-medium text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $sub->frequency_label }}
                                </span>
                            </div>

                            <!-- Products in subscription -->
                            <div class="flex items-center gap-2 flex-wrap text-sm text-slate-700">
                                @foreach($sub->items as $item)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-800 rounded-lg text-xs font-medium">
                                        💧 <strong>{{ $item->quantity }}x</strong> {{ $item->product?->name ?? 'Water Can' }} (₹{{ number_format($item->unit_price, 2) }})
                                    </span>
                                @endforeach
                            </div>

                            <!-- Next Delivery & Details -->
                            <div class="flex items-center gap-4 text-xs text-slate-500 flex-wrap">
                                <div>
                                    <span>Next Delivery:</span>
                                    <strong class="text-slate-700 ml-1">
                                        {{ $sub->next_delivery_date ? $sub->next_delivery_date->format('d M, Y') : 'N/A' }}
                                    </strong>
                                </div>
                                <div>
                                    <span>Time Slot:</span>
                                    <strong class="text-slate-700 ml-1">{{ $sub->deliverySlot?->name ?? 'Standard' }}</strong>
                                </div>
                                <div>
                                    <span>Address:</span>
                                    <span class="text-slate-700 ml-1">{{ $sub->deliveryAddress?->area }}, {{ $sub->deliveryAddress?->pincode }}</span>
                                </div>
                                <div>
                                    <span>Payment:</span>
                                    <strong class="text-slate-700 ml-1 uppercase">{{ $sub->payment_method }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 self-start md:self-center">
                            <a href="{{ route('customer.subscriptions.show', $sub) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                                View Details
                            </a>

                            @if($sub->status === 'active')
                                <form action="{{ route('customer.subscriptions.pause', $sub) }}" method="POST" onsubmit="return confirm('Pause this recurring subscription? You can resume anytime.');">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-colors">
                                        Pause
                                    </button>
                                </form>
                            @elseif($sub->status === 'paused')
                                <form action="{{ route('customer.subscriptions.resume', $sub) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                        Resume
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
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
