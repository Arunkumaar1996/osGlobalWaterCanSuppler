@extends('layouts.admin')

@section('title', 'Customer Orders - Admin')
@section('page_title', 'Customer Orders & Dispatches')
@section('page_subtitle', 'Independent multi-status tracking: Order lifecycle, split sourcing, payments, and delivery fleet')

@section('header_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.manifests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        <span>Dispatch Manifests</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Quick Status Pill Counters -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors {{ !request()->hasAny(['order_status', 'fulfillment_status', 'delivery_status']) ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            All Orders ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['order_status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors {{ request('order_status') === 'pending' ? 'bg-amber-600 text-white' : 'bg-white text-amber-700 border border-amber-200 hover:bg-amber-50' }}">
            Pending Action ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['fulfillment_status' => 'unfulfilled']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors {{ request('fulfillment_status') === 'unfulfilled' ? 'bg-rose-600 text-white' : 'bg-white text-rose-700 border border-rose-200 hover:bg-rose-50' }}">
            Unfulfilled Sourcing ({{ $counts['unfulfilled'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['delivery_status' => 'out_for_delivery']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors {{ request('delivery_status') === 'out_for_delivery' ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 border border-blue-200 hover:bg-blue-50' }}">
            On the Road ({{ $counts['out_for_delivery'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['delivery_status' => 'delivered']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors {{ request('delivery_status') === 'delivered' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50' }}">
            Delivered ({{ $counts['delivered'] }})
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Search Order / Customer</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Order #, name, mobile..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Fulfillment</label>
                <select name="fulfillment_status" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Fulfillment</option>
                    <option value="unfulfilled" {{ request('fulfillment_status') === 'unfulfilled' ? 'selected' : '' }}>Unfulfilled</option>
                    <option value="partially_fulfilled" {{ request('fulfillment_status') === 'partially_fulfilled' ? 'selected' : '' }}>Partially Fulfilled</option>
                    <option value="fulfilled" {{ request('fulfillment_status') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Payment</label>
                <select name="payment_status" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">All Payments</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Delivery Date</label>
                <input type="date" name="delivery_date" value="{{ request('delivery_date') }}" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Order Number</th>
                        <th class="px-6 py-3.5">Customer & Area</th>
                        <th class="px-6 py-3.5">Delivery Slot</th>
                        <th class="px-6 py-3.5">Items Summary</th>
                        <th class="px-6 py-3.5 text-center">4 Independent Statuses</th>
                        <th class="px-6 py-3.5 text-right">Amount</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-brand-600 hover:text-brand-700 block">
                                {{ $order->order_number }}
                            </a>
                            <span class="text-[11px] text-slate-400 font-sans">{{ $order->created_at->format('d M, h:i A') }}</span>
                        </td>
                        <td class="px-6 py-4 font-sans">
                            <span class="font-bold text-slate-900 block">{{ $order->customer?->user?->name ?? 'Customer' }}</span>
                            <span class="text-xs text-slate-500 font-mono">{{ $order->customer?->user?->mobile }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $order->snapshot_address['area'] ?? '' }} ({{ $order->snapshot_address['pincode'] ?? '' }})</span>
                        </td>
                        <td class="px-6 py-4 font-sans text-xs">
                            <span class="font-bold text-slate-800 block">{{ $order->delivery_date->format('d M Y') }}</span>
                            <span class="text-slate-500">{{ $order->deliverySlot?->name ?? 'Standard Slot' }}</span>
                        </td>
                        <td class="px-6 py-4 font-sans text-xs">
                            @foreach($order->items as $item)
                                <div>
                                    <span class="font-bold text-slate-900">{{ $item->quantity }}x</span> 
                                    <span class="text-slate-700">{{ $item->product_name_snapshot }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 font-sans text-center">
                            <div class="flex flex-col items-center gap-1">
                                <!-- Order Status -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                    {{ $order->order_status === 'confirmed' ? 'bg-blue-50 text-blue-700' : '' }}
                                    {{ $order->order_status === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $order->order_status === 'completed' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $order->order_status === 'cancelled' ? 'bg-rose-50 text-rose-700' : '' }}">
                                    Order: {{ ucfirst($order->order_status) }}
                                </span>
                                <!-- Fulfillment Status -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                    {{ $order->fulfillment_status === 'fulfilled' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $order->fulfillment_status === 'partially_fulfilled' ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $order->fulfillment_status === 'unfulfilled' ? 'bg-rose-50 text-rose-700' : '' }}">
                                    Sourcing: {{ ucfirst(str_replace('_', ' ', $order->fulfillment_status)) }}
                                </span>
                                <!-- Delivery Status -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                    {{ $order->delivery_status === 'delivered' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $order->delivery_status === 'out_for_delivery' ? 'bg-purple-50 text-purple-700' : '' }}
                                    {{ $order->delivery_status === 'pending' ? 'bg-slate-100 text-slate-600' : '' }}">
                                    Delivery: {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="font-black text-slate-900 text-sm">₹{{ number_format($order->total_amount, 2) }}</div>
                            <span class="text-[10px] uppercase font-bold {{ $order->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-sans">
                            <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 text-xs font-bold text-brand-600 hover:text-white bg-brand-50 hover:bg-brand-600 rounded-lg transition-colors">
                                Manage & Sourcing &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-sans">
                            No orders found matching the filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
