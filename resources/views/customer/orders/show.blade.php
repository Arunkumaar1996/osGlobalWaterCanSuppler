@extends('layouts.customer')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.orders.index') }}" class="hover:text-brand-600">Orders</a>
                <span>&rsaquo;</span>
                <span class="font-bold text-slate-800">{{ $order->order_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Order Details</h1>
        </div>

        <a href="{{ route('customer.orders.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
            &larr; Back to Order History
        </a>
    </div>

    <!-- Status Tracking Timeline Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tracking Number</span>
                <p class="text-lg font-black text-slate-900 mt-0.5">{{ $order->order_number }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                    {{ $order->order_status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                      ($order->order_status === 'out_for_delivery' ? 'bg-sky-50 text-sky-700 border border-sky-200' :
                      ($order->order_status === 'delivered' ? 'bg-slate-100 text-slate-700' : 'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                    Status: {{ str_replace('_', ' ', $order->order_status) }}
                </span>
            </div>
        </div>

        <!-- 4 Step Visual Progress Bar -->
        <div class="py-4">
            <div class="grid grid-cols-4 text-center text-xs font-bold">
                <div class="space-y-2">
                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center bg-emerald-600 text-white shadow-sm">
                        &check;
                    </div>
                    <span class="text-slate-900 block text-[11px]">Placed</span>
                </div>
                <div class="space-y-2">
                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center {{ in_array($order->order_status, ['confirmed', 'processing', 'out_for_delivery', 'delivered']) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-600' }}">
                        {{ in_array($order->order_status, ['confirmed', 'processing', 'out_for_delivery', 'delivered']) ? '✓' : '2' }}
                    </div>
                    <span class="text-slate-900 block text-[11px]">Confirmed</span>
                </div>
                <div class="space-y-2">
                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center {{ in_array($order->order_status, ['out_for_delivery', 'delivered']) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-600' }}">
                        {{ in_array($order->order_status, ['out_for_delivery', 'delivered']) ? '✓' : '3' }}
                    </div>
                    <span class="text-slate-900 block text-[11px]">Out for Delivery</span>
                </div>
                <div class="space-y-2">
                    <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center {{ $order->order_status === 'delivered' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-600' }}">
                        {{ $order->order_status === 'delivered' ? '✓' : '4' }}
                    </div>
                    <span class="text-slate-900 block text-[11px]">Delivered</span>
                </div>
            </div>
        </div>

        <!-- Scheduled Delivery Window Banner -->
        <div class="p-4 rounded-2xl bg-brand-50/70 border border-brand-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div>
                <span class="text-brand-800 font-bold block">Scheduled Delivery</span>
                <span class="text-brand-900 text-sm font-black">{{ $order->delivery_date->format('l, d F Y') }}</span>
            </div>
            <div>
                <span class="text-brand-800 font-bold block">Delivery Window</span>
                <span class="text-brand-900 text-sm font-black">{{ $order->deliverySlot?->name ?? 'Standard Window' }}</span>
            </div>
        </div>
    </div>

    <!-- Items & Address Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <!-- Delivery Address Snapshot -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                Delivery Address
            </h3>
            <div class="text-xs space-y-1">
                <p class="font-bold text-slate-900 text-sm">{{ $order->snapshot_address['contact_name'] ?? 'Recipient' }}</p>
                <p class="text-slate-500">Phone: {{ $order->snapshot_address['contact_mobile'] ?? 'N/A' }}</p>
                <p class="text-slate-600 leading-relaxed pt-1">
                    {{ $order->snapshot_address['house_flat_no'] ?? '' }},
                    {{ $order->snapshot_address['building'] ?? '' }},
                    {{ $order->snapshot_address['street'] ?? '' }},
                    {{ $order->snapshot_address['area'] ?? '' }},
                    {{ $order->snapshot_address['city'] ?? '' }} - {{ $order->snapshot_address['pincode'] ?? '' }}
                </p>
                @if(!empty($order->snapshot_address['instructions']))
                    <p class="text-slate-400 pt-2 italic">Driver Note: {{ $order->snapshot_address['instructions'] }}</p>
                @endif
            </div>
        </div>

        <!-- Payment & Financial Snapshot -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3" x-data="{
            loading: false,
            errorMessage: '',
            payOrder() {
                this.loading = true;
                this.errorMessage = '';

                fetch('{{ route('customer.orders.checkout', $order->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (!data.success) {
                        this.errorMessage = data.message || 'Payment initiation failed.';
                        return;
                    }

                    const options = {
                        key: data.key,
                        amount: data.amount,
                        currency: data.currency,
                        name: 'AquaFlow Water Delivery',
                        description: 'Payment for Order #' + data.order_number,
                        order_id: data.razorpay_order_id,
                        prefill: {
                            name: data.customer_name,
                            email: data.customer_email,
                            contact: data.customer_mobile
                        },
                        theme: {
                            color: '#0284c7'
                        },
                        handler: function (response) {
                            document.getElementById('order_razorpay_order_id').value = response.razorpay_order_id;
                            document.getElementById('order_razorpay_payment_id').value = response.razorpay_payment_id;
                            document.getElementById('order_razorpay_signature').value = response.razorpay_signature;
                            document.getElementById('order_verify_form').submit();
                        }
                    };

                    if (typeof Razorpay !== 'undefined') {
                        const rzp = new Razorpay(options);
                        rzp.open();
                    } else {
                        alert('Razorpay Checkout Initialized: ' + data.razorpay_order_id + '\nMock verifying payment...');
                        document.getElementById('order_razorpay_order_id').value = data.razorpay_order_id;
                        document.getElementById('order_razorpay_payment_id').value = 'pay_sim_' + Math.random().toString(36).substring(7);
                        document.getElementById('order_razorpay_signature').value = 'mock_valid_signature';
                        document.getElementById('order_verify_form').submit();
                    }
                })
                .catch(err => {
                    this.loading = false;
                    this.errorMessage = 'Network error: ' + err.message;
                });
            }
        }">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                Payment Details
            </h3>
            <div class="text-xs space-y-2">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Payment Method</span>
                    <span class="font-bold uppercase text-slate-900">{{ str_replace('_', ' ', $order->payment_method_preferred) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Payment Status</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                        {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $order->payment_status }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-800">₹{{ number_format($order->subtotal_amount, 2) }}</span>
                </div>
                @if($order->deposit_amount > 0)
                    <div class="flex items-center justify-between text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200">
                        <span>Refundable Can Deposit</span>
                        <span class="font-bold">+₹{{ number_format($order->deposit_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between text-slate-600">
                    <span>Can Exchange</span>
                    <span class="font-semibold text-slate-900">
                        {{ $order->has_empty_cans_to_exchange ? '1:1 Exchange (' . $order->empty_cans_expected . ' empties)' : 'New Customer (No empties required)' }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Delivery Charge</span>
                    <span class="font-bold text-slate-800">{{ $order->delivery_charge == 0 ? 'FREE' : '₹' . number_format($order->delivery_charge, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Amount Paid</span>
                    <span class="font-bold text-emerald-600">₹{{ number_format($order->paid_amount, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-sm font-black text-slate-900">
                    <span>Total Amount</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>

                @php
                    $orderRemaining = max(0, (float) $order->total_amount - (float) $order->paid_amount);
                @endphp

                @if($orderRemaining > 0)
                    <div class="pt-3 border-t border-slate-100">
                        <div x-show="errorMessage" class="mb-2 p-2 rounded bg-rose-50 text-rose-700 text-[11px] font-semibold" x-text="errorMessage"></div>
                        <button type="button" @click="payOrder()" :disabled="loading" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition-colors disabled:opacity-50">
                            <span x-show="!loading">Pay Due ₹{{ number_format($orderRemaining, 2) }} with Razorpay</span>
                            <span x-show="loading">Connecting...</span>
                        </button>
                    </div>

                    <form id="order_verify_form" method="POST" action="{{ route('customer.orders.verify', $order->id) }}" class="hidden">
                        @csrf
                        <input type="hidden" name="razorpay_order_id" id="order_razorpay_order_id">
                        <input type="hidden" name="razorpay_payment_id" id="order_razorpay_payment_id">
                        <input type="hidden" name="razorpay_signature" id="order_razorpay_signature">
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 mb-4">
            Ordered Items
        </h3>
        <div class="divide-y divide-slate-100 text-xs">
            @foreach($order->items as $item)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-900 block text-sm">{{ $item->product_name_snapshot }}</span>
                        <span class="text-slate-500 block text-[11px] mt-0.5">₹{{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }} units</span>
                    </div>
                    <span class="font-bold text-slate-900 text-sm">₹{{ number_format($item->subtotal, 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endsection
