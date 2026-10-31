@extends('layouts.customer')

@section('title', 'Pay Outstanding Dues')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('customer.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                <span>&rsaquo;</span>
                <span class="font-bold text-slate-800">Accounts & Dues</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Outstanding Balance & Dues</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('customer.ledger.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                View Financial Passbook
            </a>
        </div>
    </div>

    <!-- Balance Summary & Quick Settlement Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
        <!-- Balance Overview -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-3xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 font-bold block">Current Net Due</span>
                <p class="text-3xl font-black mt-2 tracking-tight">
                    ₹{{ number_format($profile->outstanding_financial_balance, 2) }}
                </p>
                <div class="mt-4 pt-4 border-t border-slate-700/60 text-xs space-y-1.5 text-slate-300">
                    <div class="flex justify-between">
                        <span>Approved Credit Limit:</span>
                        <span class="font-bold text-white">₹{{ number_format($profile->credit_limit, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pay Later Facility:</span>
                        <span class="font-bold {{ $profile->allow_pay_later ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $profile->allow_pay_later ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-[11px] text-slate-400">
                &bull; Empty can container balances are maintained separately and never mixed with financial dues.
            </div>
        </div>

        <!-- Settle Balance Form -->
        <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm" x-data="{
            amount: '{{ $profile->outstanding_financial_balance > 0 ? $profile->outstanding_financial_balance : 100 }}',
            loading: false,
            errorMessage: '',
            setAmount(val) {
                this.amount = val;
            },
            initiatePayment() {
                if (this.amount <= 0) {
                    this.errorMessage = 'Please enter an amount greater than ₹0.';
                    return;
                }
                this.loading = true;
                this.errorMessage = '';

                fetch('{{ route('customer.dues.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ amount: this.amount })
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (!data.success) {
                        this.errorMessage = data.message || 'Payment initialization failed.';
                        return;
                    }

                    // Open Razorpay Standard Checkout
                    const options = {
                        key: data.key,
                        amount: data.amount,
                        currency: data.currency,
                        name: 'AquaFlow Water Delivery',
                        description: 'Account Dues Settlement',
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
                            // Submit verification form
                            document.getElementById('settle_razorpay_order_id').value = response.razorpay_order_id;
                            document.getElementById('settle_razorpay_payment_id').value = response.razorpay_payment_id;
                            document.getElementById('settle_razorpay_signature').value = response.razorpay_signature;
                            document.getElementById('settle_amount').value = data.amount_rupees;
                            document.getElementById('settlement_verify_form').submit();
                        }
                    };

                    if (typeof Razorpay !== 'undefined') {
                        const rzp = new Razorpay(options);
                        rzp.open();
                    } else {
                        // Fallback simulation for sandbox without live script
                        alert('Razorpay Checkout Initialized: Order ID ' + data.razorpay_order_id + '\nMock verifying payment...');
                        document.getElementById('settle_razorpay_order_id').value = data.razorpay_order_id;
                        document.getElementById('settle_razorpay_payment_id').value = 'pay_sim_' + Math.random().toString(36).substring(7);
                        document.getElementById('settle_razorpay_signature').value = 'mock_valid_signature';
                        document.getElementById('settle_amount').value = data.amount_rupees;
                        document.getElementById('settlement_verify_form').submit();
                    }
                })
                .catch(err => {
                    this.loading = false;
                    this.errorMessage = 'Network error: ' + err.message;
                });
            }
        }">
            <h3 class="text-base font-bold text-slate-900 mb-1">Instant Online Settlement</h3>
            <p class="text-xs text-slate-500 mb-4">Pay full or partial amount via UPI, Cards, NetBanking or Wallets through Razorpay</p>

            <div x-show="errorMessage" class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="errorMessage"></div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Enter Amount to Pay (₹)</label>
                    <div class="relative rounded-2xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 font-bold">
                            ₹
                        </div>
                        <input type="number" step="0.01" min="1" x-model="amount" class="w-full pl-8 pr-4 py-3 rounded-2xl border border-slate-200 text-lg font-bold text-slate-900 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>

                <!-- Quick Preset Chips -->
                <div class="flex flex-wrap gap-2 text-xs">
                    @if($profile->outstanding_financial_balance > 0)
                        <button type="button" @click="setAmount({{ $profile->outstanding_financial_balance }})" class="px-3 py-1.5 rounded-xl bg-brand-50 text-brand-700 font-bold border border-brand-200 hover:bg-brand-100 transition-colors">
                            Pay Full Due (₹{{ number_format($profile->outstanding_financial_balance, 2) }})
                        </button>
                    @endif
                    <button type="button" @click="setAmount(200)" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-colors">
                        ₹200
                    </button>
                    <button type="button" @click="setAmount(500)" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-colors">
                        ₹500
                    </button>
                    <button type="button" @click="setAmount(1000)" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-colors">
                        ₹1,000
                    </button>
                </div>

                <div class="pt-2">
                    <button type="button" @click="initiatePayment()" :disabled="loading || amount <= 0" class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all disabled:opacity-50">
                        <span x-show="!loading">Proceed to Pay ₹<span x-text="amount"></span> via Razorpay</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Connecting to Gateway...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Hidden Verification POST Form -->
            <form id="settlement_verify_form" method="POST" action="{{ route('customer.dues.verify') }}" class="hidden">
                @csrf
                <input type="hidden" name="razorpay_order_id" id="settle_razorpay_order_id">
                <input type="hidden" name="razorpay_payment_id" id="settle_razorpay_payment_id">
                <input type="hidden" name="razorpay_signature" id="settle_razorpay_signature">
                <input type="hidden" name="amount" id="settle_amount">
            </form>
        </div>
    </div>

    <!-- Unpaid / Partially Paid Orders Section -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Pending Order Payments</h3>
                <p class="text-xs text-slate-500">Orders placed on Pay Later credit or pending payment settlement</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                {{ $unpaidOrders->count() }} Pending
            </span>
        </div>

        @if($unpaidOrders->isEmpty())
            <div class="py-8 text-center text-slate-500 text-xs">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 font-bold text-lg">
                    &check;
                </div>
                <p class="font-bold text-slate-800 text-sm">All Orders are Fully Paid!</p>
                <p class="text-slate-500 mt-1">You have no pending invoices or outstanding order dues.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-3">Order #</th>
                            <th class="py-3 px-3">Date</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Total</th>
                            <th class="py-3 px-3 text-right">Paid</th>
                            <th class="py-3 px-3 text-right">Remaining Due</th>
                            <th class="py-3 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($unpaidOrders as $ord)
                            @php
                                $due = max(0, (float) $ord->total_amount - (float) $ord->paid_amount);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 font-bold text-slate-900">
                                    <a href="{{ route('customer.orders.show', $ord->id) }}" class="text-brand-600 hover:underline">
                                        {{ $ord->order_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $ord->created_at->format('d M Y') }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $ord->payment_status === 'partially_paid' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ str_replace('_', ' ', $ord->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-semibold text-slate-800">
                                    ₹{{ number_format($ord->total_amount, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-semibold text-emerald-600">
                                    ₹{{ number_format($ord->paid_amount, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-rose-600">
                                    ₹{{ number_format($due, 2) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('customer.orders.show', $ord->id) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-[11px] shadow-sm transition-colors">
                                        Pay ₹{{ number_format($due, 2) }}
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

<!-- Razorpay Standard Checkout Script (with graceful offline fallback) -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endsection
