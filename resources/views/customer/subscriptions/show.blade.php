@extends('layouts.customer')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.dashboard') }}" class="hover:text-brand-600">Home</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('customer.subscriptions.index') }}" class="hover:text-brand-600">Subscriptions</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">#{{ $subscription->subscription_number }}</span>
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
            <p class="text-sm text-slate-500 mt-1">Configured on {{ $subscription->created_at->format('d M, Y') }}</p>
        </div>

        <!-- Action Controls -->
        <div class="flex items-center gap-2">
            @if($subscription->status === 'active')
                <form action="{{ route('customer.subscriptions.pause', $subscription) }}" method="POST" onsubmit="return confirm('Are you sure you want to pause your recurring water deliveries?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-colors">
                        Pause Plan
                    </button>
                </form>
            @elseif($subscription->status === 'paused')
                <form action="{{ route('customer.subscriptions.resume', $subscription) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                        Resume Plan
                    </button>
                </form>
            @endif

            @if(!in_array($subscription->status, ['cancelled', 'completed']))
                <form action="{{ route('customer.subscriptions.cancel', $subscription) }}" method="POST" onsubmit="return confirm('Cancel this subscription? You can always create a new one later.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                        Cancel Subscription
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Next Delivery Info Card -->
        <div class="bg-gradient-to-br from-brand-600 to-cyan-600 rounded-2xl p-6 text-white shadow-md">
            <p class="text-xs font-medium uppercase tracking-wider text-brand-100">Scheduled Dispatch</p>
            <p class="text-3xl font-extrabold mt-2">
                {{ $subscription->next_delivery_date ? $subscription->next_delivery_date->format('d M, Y') : 'N/A' }}
            </p>
            <div class="mt-4 pt-4 border-t border-brand-400/30 text-xs text-brand-50 space-y-1">
                <p><strong>Frequency:</strong> {{ $subscription->frequency_label }}</p>
                <p><strong>Slot:</strong> {{ $subscription->deliverySlot?->name ?? 'Standard' }} ({{ $subscription->deliverySlot?->formatted_time_range ?? '' }})</p>
                <p><strong>Billing:</strong> {{ strtoupper($subscription->payment_method) }}</p>
            </div>
        </div>

        <!-- Delivery Address Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Delivery Address</h3>
            @if($subscription->deliveryAddress)
                <p class="font-bold text-slate-900 text-sm">{{ $subscription->deliveryAddress->contact_person_name }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $subscription->deliveryAddress->contact_person_mobile }}</p>
                <p class="text-xs text-slate-600 mt-2">
                    {{ $subscription->deliveryAddress->house_flat_no }}, {{ $subscription->deliveryAddress->building_apartment_name ?? '' }}<br>
                    {{ $subscription->deliveryAddress->street_address }}, {{ $subscription->deliveryAddress->area }}<br>
                    {{ $subscription->deliveryAddress->city }}, {{ $subscription->deliveryAddress->state }} - {{ $subscription->deliveryAddress->pincode }}
                </p>
            @else
                <p class="text-xs text-slate-400">Address record unavailable.</p>
            @endif
        </div>

        <!-- Subscription Items Summary -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Subscribed Products</h3>
            <div class="space-y-3">
                @foreach($subscription->items as $item)
                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100 last:border-none">
                        <div>
                            <p class="font-bold text-slate-800">{{ $item->product?->name ?? 'Water Can' }}</p>
                            <p class="text-slate-400">₹{{ number_format($item->unit_price, 2) }} each</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2 py-1 rounded bg-slate-100 font-bold text-slate-700">
                                {{ $item->quantity }} can(s)
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Generated Orders History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Recent Deliveries / Orders</h2>
                <p class="text-xs text-slate-500">Orders automatically scheduled under this subscription.</p>
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ $subscription->orders->count() }} total orders</span>
        </div>

        @if($subscription->orders->isEmpty())
            <div class="p-8 text-center text-slate-500 text-xs">
                No orders have been dispatched for this subscription cycle yet. Deliveries will commence on {{ $subscription->next_delivery_date?->format('d M, Y') }}.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Order Number</th>
                            <th class="px-6 py-3">Delivery Date</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Delivery</th>
                            <th class="px-6 py-3">Total Amount</th>
                            <th class="px-6 py-3 text-right">Action</th>
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
                                <td class="px-6 py-3.5 font-bold text-slate-900">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="{{ route('customer.orders.show', $order) }}" class="text-brand-600 hover:text-brand-800 font-bold">
                                        View
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
