@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('admin.subscriptions.index') }}" class="hover:text-brand-600">Subscriptions</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">{{ $subscription->subscription_number }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Subscription #{{ $subscription->subscription_number }}</h1>
                @if($subscription->status === 'active')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active
                    </span>
                @elseif($subscription->status === 'paused')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        Paused
                    </span>
                @elseif($subscription->status === 'cancelled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                        Cancelled
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                        {{ ucfirst($subscription->status) }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">Created on {{ $subscription->created_at->format('d M, Y \a\t g:i A') }}</p>
        </div>

        <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors">
            &larr; Back to Subscriptions
        </a>
    </div>

    <!-- Metadata & Address Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Cadence & Schedule Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Schedule & Frequency</h3>
            <div class="space-y-2 text-xs text-slate-700">
                <p><strong>Frequency:</strong> {{ $subscription->frequency_label }}</p>
                <p><strong>Next Delivery:</strong> <span class="font-bold text-emerald-600">{{ $subscription->next_delivery_date ? $subscription->next_delivery_date->format('d M, Y') : 'N/A' }}</span></p>
                <p><strong>Last Generated:</strong> {{ $subscription->last_generated_date ? $subscription->last_generated_date->format('d M, Y') : 'None' }}</p>
                <p><strong>Start Date:</strong> {{ $subscription->start_date ? $subscription->start_date->format('d M, Y') : 'N/A' }}</p>
                <p><strong>End Date:</strong> {{ $subscription->end_date ? $subscription->end_date->format('d M, Y') : 'Ongoing' }}</p>
                <p><strong>Delivery Slot:</strong> {{ $subscription->deliverySlot?->name ?? 'Standard' }} ({{ $subscription->deliverySlot?->formatted_time_range ?? '' }})</p>
                <p><strong>Payment Mode:</strong> <span class="uppercase font-bold">{{ $subscription->payment_method }}</span></p>
            </div>
        </div>

        <!-- Customer Profile Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Customer Details</h3>
            <div class="text-xs text-slate-700 space-y-1.5">
                <p class="font-bold text-sm text-slate-900">{{ $subscription->customer?->user?->name ?? 'N/A' }}</p>
                <p><strong>Mobile:</strong> {{ $subscription->customer?->user?->mobile }}</p>
                <p><strong>Email:</strong> {{ $subscription->customer?->user?->email }}</p>
                <p><strong>Branch:</strong> {{ $subscription->branch?->name ?? 'Headquarters' }}</p>
                @if($subscription->customer_notes)
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-slate-600 mt-2">
                        <strong class="text-slate-800">Special Notes:</strong> {{ $subscription->customer_notes }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Delivery Address Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Delivery Address</h3>
            @if($subscription->deliveryAddress)
                <div class="text-xs text-slate-700 space-y-1">
                    <p class="font-bold text-slate-900">{{ $subscription->deliveryAddress->contact_person_name }}</p>
                    <p class="text-slate-500">{{ $subscription->deliveryAddress->contact_person_mobile }}</p>
                    <p class="text-slate-600 mt-2">
                        {{ $subscription->deliveryAddress->house_flat_no }}, {{ $subscription->deliveryAddress->building_apartment_name ?? '' }}<br>
                        {{ $subscription->deliveryAddress->street_address }}, {{ $subscription->deliveryAddress->area }}<br>
                        {{ $subscription->deliveryAddress->city }}, {{ $subscription->deliveryAddress->state }} - {{ $subscription->deliveryAddress->pincode }}
                    </p>
                </div>
            @else
                <p class="text-xs text-slate-400">No address record.</p>
            @endif
        </div>
    </div>

    <!-- Subscribed Items -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900">Subscribed Water Products</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Product Name</th>
                        <th class="px-6 py-3">Can Type</th>
                        <th class="px-6 py-3">Quantity per Cycle</th>
                        <th class="px-6 py-3">Unit Price</th>
                        <th class="px-6 py-3 text-right">Subtotal per Cycle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($subscription->items as $item)
                        <tr>
                            <td class="px-6 py-3.5 font-bold text-slate-900">{{ $item->product?->name ?? 'Water Can' }}</td>
                            <td class="px-6 py-3.5">{{ $item->product?->canType?->name ?? 'Standard' }}</td>
                            <td class="px-6 py-3.5 font-bold text-slate-800">{{ $item->quantity }} can(s)</td>
                            <td class="px-6 py-3.5">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-900">₹{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders Generated -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Generated Recurring Orders History</h2>
            <span class="text-xs font-semibold text-slate-500">{{ $subscription->orders->count() }} orders generated</span>
        </div>
        @if($subscription->orders->isEmpty())
            <div class="p-8 text-center text-slate-500 text-xs">
                No orders have been generated from this subscription yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Order Number</th>
                            <th class="px-6 py-3">Delivery Date</th>
                            <th class="px-6 py-3">Order Status</th>
                            <th class="px-6 py-3">Delivery Status</th>
                            <th class="px-6 py-3">Payment</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($subscription->orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3.5 font-bold text-slate-900">#{{ $order->order_number }}</td>
                                <td class="px-6 py-3.5">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d M, Y') }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $order->delivery_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($order->delivery_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-bold {{ $order->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </td>
                                <td class="px-6 py-3.5 font-bold text-slate-900">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-3.5 text-right font-medium">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 hover:text-brand-900 font-bold">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
