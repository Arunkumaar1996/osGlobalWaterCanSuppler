@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number . ' - Fulfillment Hub')
@section('page_title', 'Order ' . $order->order_number)
@section('page_subtitle', 'Placed on ' . $order->created_at->format('d M Y, h:i A') . ' by ' . ($order->customer?->user?->name ?? 'Customer'))

@section('header_actions')
<div class="flex items-center gap-2" x-data="{ statusModal: false, cancelModal: false }">
    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Back to Orders</span>
    </a>

    @if($order->order_status !== 'cancelled')
    <button type="button" @click="statusModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-sm hover:bg-slate-800 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        <span>Update Status</span>
    </button>
    <button type="button" @click="cancelModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-rose-700 font-semibold text-sm hover:bg-rose-100 transition-colors">
        <span>Cancel Order</span>
    </button>
    @endif

    <!-- Update Status Modal -->
    <div x-show="statusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="statusModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4" @click.away="statusModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Update Order Status Independently</h3>
                <button type="button" @click="statusModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Status Domain</label>
                    <select name="status_type" id="status_type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl" onchange="updateStatusOptions(this.value)">
                        <option value="order_status">Order Lifecycle Status</option>
                        <option value="delivery_status">Delivery Status</option>
                        <option value="payment_status">Payment Status</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">New Value</label>
                    <select name="new_status" id="new_status" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Audit Comment / Reason</label>
                    <input type="text" name="comment" placeholder="Reason for status change" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="statusModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl hover:bg-brand-700">
                        Save Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div x-show="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="cancelModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4" @click.away="cancelModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-rose-700">Cancel Order {{ $order->order_number }}</h3>
                <button type="button" @click="cancelModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="space-y-4">
                @csrf
                <p class="text-xs text-slate-600">
                    Cancelling this order will release all reserved warehouse stocks and reverse pending fulfillments.
                </p>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cancellation Reason <span class="text-rose-500">*</span></label>
                    <input type="text" name="reason" required placeholder="e.g. Customer requested cancellation" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="cancelModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                        Back
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 text-white text-xs font-bold rounded-xl hover:bg-rose-700">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- 4 Independent Statuses Banner -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">1. Order Lifecycle</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                {{ $order->order_status === 'confirmed' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                {{ $order->order_status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                {{ $order->order_status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                {{ $order->order_status === 'cancelled' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}">
                {{ ucfirst($order->order_status) }}
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">2. Sourcing & Fulfillment</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                {{ $order->fulfillment_status === 'fulfilled' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                {{ $order->fulfillment_status === 'partially_fulfilled' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                {{ $order->fulfillment_status === 'unfulfilled' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}">
                {{ ucfirst(str_replace('_', ' ', $order->fulfillment_status)) }}
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">3. Payment Settlement</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                {{ $order->payment_status === 'unpaid' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                {{ $order->payment_status === 'refunded' ? 'bg-slate-100 text-slate-700' : '' }}">
                {{ ucfirst($order->payment_status) }} ({{ strtoupper($order->payment_method_preferred) }})
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">4. Delivery Dispatch</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                {{ $order->delivery_status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                {{ $order->delivery_status === 'out_for_delivery' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                {{ $order->delivery_status === 'pending' ? 'bg-slate-100 text-slate-700' : '' }}">
                {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
            </span>
        </div>
    </div>

    <!-- Customer, Address & Financial Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Delivery Target -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Customer & Address Snapshot</h4>
            <div class="text-xs space-y-1">
                <span class="font-bold text-sm text-slate-900 block">{{ $order->customer?->user?->name }}</span>
                <span class="text-slate-500 font-mono block">{{ $order->customer?->user?->mobile }} | {{ $order->customer?->user?->email }}</span>
                <div class="pt-2 text-slate-700 leading-relaxed font-sans">
                    @if(!empty($order->snapshot_address['house_flat_no']))
                        {{ $order->snapshot_address['house_flat_no'] }}, {{ $order->snapshot_address['building'] ?? '' }}<br>
                        {{ $order->snapshot_address['street'] ?? '' }}<br>
                    @elseif(!empty($order->snapshot_address['address_line1']))
                        {{ $order->snapshot_address['address_line1'] }} {{ $order->snapshot_address['address_line2'] ?? '' }}<br>
                    @endif
                    {{ $order->snapshot_address['area'] ?? '' }}, {{ $order->snapshot_address['city'] ?? '' }} - {{ $order->snapshot_address['pincode'] ?? '' }}
                </div>
                @if(!empty($order->snapshot_address['instructions']))
                    <div class="text-[11px] text-amber-700 pt-1">Note: {{ $order->snapshot_address['instructions'] }}</div>
                @elseif(!empty($order->snapshot_address['landmark']))
                    <div class="text-[11px] text-slate-400 pt-1">Landmark: {{ $order->snapshot_address['landmark'] }}</div>
                @endif
            </div>
        </div>

        <!-- Schedule & Branch -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Delivery Schedule & Slot</h4>
            <div class="text-xs space-y-2">
                <div>
                    <span class="text-slate-400 block">Scheduled Delivery Date:</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $order->delivery_date->format('l, d F Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Time Window:</span>
                    <span class="font-semibold text-slate-800">{{ $order->deliverySlot?->name }} ({{ $order->deliverySlot?->start_time }} - {{ $order->deliverySlot?->end_time }})</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Fulfillment Branch:</span>
                    <span class="font-semibold text-slate-800">{{ $order->branch?->name }}</span>
                </div>
                @if($order->customer_notes)
                <div class="p-2 bg-amber-50 rounded-lg text-amber-800 text-[11px]">
                    <span class="font-bold">Instructions:</span> {{ $order->customer_notes }}
                </div>
                @endif
            </div>
        </div>

        <!-- Financial Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Financial Breakdown</h4>
            <div class="text-xs space-y-1.5 font-mono">
                <div class="flex justify-between text-slate-600 font-sans">
                    <span>Products Subtotal</span>
                    <span>₹{{ number_format($order->subtotal_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600 font-sans">
                    <span>Delivery Charges</span>
                    <span>₹{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                @if($order->deposit_amount > 0)
                <div class="flex justify-between text-amber-700 bg-amber-50 p-1.5 rounded-lg border border-amber-200 text-[11px] font-sans">
                    <span>Refundable Bottle Deposit</span>
                    <span>+₹{{ number_format($order->deposit_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-slate-600 text-[11px] font-sans">
                    <span>Can Exchange:</span>
                    <span>{{ $order->has_empty_cans_to_exchange ? '1:1 Return (' . $order->empty_cans_expected . ' empties)' : 'New Customer (0 empties)' }}</span>
                </div>
                <div class="flex justify-between text-slate-600 font-sans">
                    <span>Tax (GST)</span>
                    <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-100 font-bold text-sm text-slate-900">
                    <span class="font-sans">Order Total</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                @if($order->previous_outstanding_at_order > 0)
                <div class="flex justify-between text-rose-600 text-[11px] font-sans pt-1">
                    <span>Prior Due at Order:</span>
                    <span>₹{{ number_format($order->previous_outstanding_at_order, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Ordered Items Sourcing Status</h3>
                <p class="text-xs text-slate-500">Track quantities ordered, allocated to fulfillments, and remaining unfulfilled</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Product Item</th>
                        <th class="px-6 py-3.5 text-center">Ordered</th>
                        <th class="px-6 py-3.5 text-center">Allocated (Fulfilled)</th>
                        <th class="px-6 py-3.5 text-center">Unfulfilled</th>
                        <th class="px-6 py-3.5 text-center">Delivered</th>
                        <th class="px-6 py-3.5 text-right">Unit Price</th>
                        <th class="px-6 py-3.5 text-right">Total Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @foreach($order->items as $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-4 font-sans">
                            <span class="font-bold text-slate-900 block">{{ $item->product_name_snapshot }}</span>
                            <span class="text-xs text-slate-400 font-mono">{{ $item->product?->sku }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-black text-sm text-slate-900">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-emerald-600">
                            {{ $item->fulfilled_quantity }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold {{ $item->unfulfilled_quantity > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            {{ $item->unfulfilled_quantity }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-blue-600">
                            {{ $item->delivered_quantity }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-700">
                            ₹{{ number_format($item->unit_price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">
                            ₹{{ number_format($item->subtotal, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sourcing Allocations (Existing Fulfillments) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Split Sourcing & Fulfillments</h3>
                <p class="text-xs text-slate-500">Consignments allocated to warehouse stock or external suppliers</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($order->fulfillments as $ful)
            <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-slate-900">{{ $ful->fulfillment_number }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                            {{ $ful->source_type === 'warehouse' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                            {{ $ful->source_type === 'warehouse' ? 'Internal Warehouse' : 'External Supplier' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                            {{ $ful->status === 'allocated' ? 'bg-amber-50 text-amber-700' : '' }}
                            {{ $ful->status === 'ready_for_dispatch' ? 'bg-indigo-50 text-indigo-700' : '' }}
                            {{ $ful->status === 'dispatched' ? 'bg-emerald-50 text-emerald-700' : '' }}
                            {{ $ful->status === 'cancelled' ? 'bg-rose-50 text-rose-700' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $ful->status)) }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-600">
                        @if($ful->source_type === 'warehouse')
                            Source: <span class="font-semibold text-slate-900">{{ $ful->warehouse?->name }}</span> (Stock reserved)
                        @else
                            Source: <span class="font-semibold text-slate-900">{{ $ful->supplier?->company_name }}</span> ({{ $ful->supplier?->phone }})
                        @endif
                    </div>

                    <div class="text-xs text-slate-700 font-sans pt-1">
                        Items: 
                        @foreach($ful->items as $fItem)
                            <span class="font-bold">{{ $fItem->quantity }}x</span> {{ $fItem->product?->name }}@if(!$loop->last), @endif
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if(in_array($ful->status, ['allocated', 'ready_for_dispatch']))
                    <form method="POST" action="{{ route('admin.fulfillments.dispatch', $ful) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition-colors">
                            Dispatch Consignment
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.fulfillments.cancel', $ful) }}" onsubmit="return confirm('Cancel this fulfillment and release reserved stock?');" class="inline">
                        @csrf
                        <input type="hidden" name="reason" value="Cancelled from order management console">
                        <button type="submit" class="px-3 py-1.5 bg-slate-100 text-rose-600 font-bold text-xs rounded-xl hover:bg-rose-50 transition-colors">
                            Cancel & Release
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-xs font-sans">
                No fulfillments allocated yet. Use the sourcing allocation form below.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Split-Fulfillment Allocation Form (Only shown if unfulfilled items remain) -->
    @if($order->order_status !== 'cancelled' && $order->items->sum('unfulfilled_quantity') > 0)
    <div class="bg-white rounded-2xl border border-brand-200 p-6 shadow-sm space-y-6" x-data="{ sourceType: 'warehouse' }">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Allocate Sourcing (Warehouse vs Supplier)</h3>
                <p class="text-xs text-slate-500">Split-fulfillment engine: allocate required cans from warehouse inventory or dropship from supplier</p>
            </div>
            <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200">
                {{ $order->items->sum('unfulfilled_quantity') }} cans awaiting sourcing
            </span>
        </div>

        <form method="POST" action="{{ route('admin.orders.fulfillments.store', $order) }}" class="space-y-6">
            @csrf

            <!-- Sourcing Source Selector -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="p-4 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3" :class="sourceType === 'warehouse' ? 'border-brand-500 bg-brand-50/30' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="source_type" value="warehouse" x-model="sourceType" class="mt-1 text-brand-600 focus:ring-brand-500">
                    <div>
                        <span class="font-bold text-sm text-slate-900 block">Fulfill from Own Warehouse Stock</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Locks reserved stock and deducts from warehouse inventory on dispatch.</span>
                    </div>
                </label>

                <label class="p-4 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-3" :class="sourceType === 'supplier' ? 'border-brand-500 bg-brand-50/30' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="source_type" value="supplier" x-model="sourceType" class="mt-1 text-brand-600 focus:ring-brand-500">
                    <div>
                        <span class="font-bold text-sm text-slate-900 block">Source Directly from Water Supplier</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Dispatches dropship consignment through registered supplier.</span>
                    </div>
                </label>
            </div>

            <!-- Specific Facility / Supplier Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div x-show="sourceType === 'warehouse'">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Sourcing Warehouse <span class="text-rose-500">*</span></label>
                    <select name="source_id" :disabled="sourceType !== 'warehouse'" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="sourceType === 'supplier'" style="display: none;">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Preferred Supplier <span class="text-rose-500">*</span></label>
                    <select name="source_id" :disabled="sourceType !== 'supplier'" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        @foreach($suppliers as $supp)
                            <option value="{{ $supp->id }}">{{ $supp->company_name }} ({{ $supp->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fulfillment Dispatch Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Stage at loading dock bay 2" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <!-- Item Quantities to Allocate -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Allocate Quantities</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5">Item</th>
                                <th class="px-4 py-2.5 text-center">Ordered</th>
                                <th class="px-4 py-2.5 text-center">Unfulfilled Balance</th>
                                <th class="px-4 py-2.5 text-center w-40">Allocate From This Source</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $idx => $item)
                            <tr>
                                <td class="px-4 py-3 font-sans font-bold text-slate-900">
                                    {{ $item->product_name_snapshot }}
                                    <input type="hidden" name="items[{{ $idx }}][order_item_id]" value="{{ $item->id }}">
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-rose-600">{{ $item->unfulfilled_quantity }}</td>
                                <td class="px-4 py-3">
                                    <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $item->unfulfilled_quantity }}" min="0" max="{{ $item->unfulfilled_quantity }}" class="w-full px-3 py-1.5 text-center text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700 shadow-sm transition-colors">
                    Confirm Split Sourcing Allocation
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Audit Status History Timeline -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Order Status History & Audit Log</h3>
        <div class="space-y-4">
            @forelse($order->statusHistories as $hist)
            <div class="flex items-start gap-3 text-xs">
                <div class="w-2 h-2 rounded-full bg-brand-500 mt-1.5 shrink-0"></div>
                <div class="flex-1">
                    <span class="font-bold text-slate-900">{{ $hist->to_status }}</span>
                    @if($hist->from_status)
                        <span class="text-slate-400">(was {{ $hist->from_status }})</span>
                    @endif
                    <p class="text-slate-600 mt-0.5">{{ $hist->comment }}</p>
                </div>
                <div class="text-right text-[11px] text-slate-400 shrink-0">
                    <div>{{ $hist->created_at->format('d M Y, h:i A') }}</div>
                    <div>By: {{ $hist->changedBy?->name ?? 'System' }}</div>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400">No status changes logged yet.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
function updateStatusOptions(type) {
    const select = document.getElementById('new_status');
    select.innerHTML = '';
    let opts = [];

    if (type === 'order_status') {
        opts = ['pending', 'confirmed', 'processing', 'completed', 'cancelled'];
    } else if (type === 'delivery_status') {
        opts = ['pending', 'assigned', 'out_for_delivery', 'delivered', 'failed', 'rescheduled'];
    } else if (type === 'payment_status') {
        opts = ['unpaid', 'paid', 'partially_paid', 'refunded'];
    }

    opts.forEach(o => {
        const el = document.createElement('option');
        el.value = o;
        el.textContent = o.charAt(0).toUpperCase() + o.slice(1).replace('_', ' ');
        select.appendChild(el);
    });
}
</script>
@endsection
