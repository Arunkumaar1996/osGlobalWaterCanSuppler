@extends('layouts.admin')

@section('title', 'Payment Receipts & Audit Log')

@section('page_title', 'Payment Receipts & Financial Collections Log')

@section('page_actions')
<a href="{{ route('admin.accounts.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    &larr; Back to Accounts Overview
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Payment Method</label>
                <select name="method" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500">
                    <option value="">All Methods</option>
                    <option value="razorpay" {{ request('method') === 'razorpay' ? 'selected' : '' }}>Razorpay Online</option>
                    <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash Collection</option>
                    <option value="upi" {{ request('method') === 'upi' ? 'selected' : '' }}>UPI / QR</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cheque" {{ request('method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="successful" {{ request('status') === 'successful' ? 'selected' : '' }}>Successful</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full py-1.5 px-3 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full py-1.5 px-3 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['method', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.payments.index') }}" class="py-2 px-3 text-xs font-bold text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Payment Date</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Linked Order</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Collector / Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $pmt)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $pmt->payment_number }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $pmt->payment_date->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.accounts.customer', $pmt->customer_profile_id) }}" class="font-bold text-slate-900 hover:text-brand-600">
                                    {{ $pmt->customer?->user?->name ?? 'Customer' }}
                                </a>
                                <div class="text-[11px] text-slate-500">{{ $pmt->customer?->user?->mobile }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($pmt->order)
                                    <a href="{{ route('admin.orders.show', $pmt->order_id) }}" class="font-bold text-brand-600 hover:underline">
                                        {{ $pmt->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Account Due Settlement</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $pmt->payment_method === 'razorpay' ? 'bg-sky-50 text-sky-700 border border-sky-200' :
                                      ($pmt->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700') }}">
                                    {{ str_replace('_', ' ', $pmt->payment_method) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black text-emerald-600 text-sm">
                                ₹{{ number_format($pmt->amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $pmt->status === 'successful' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $pmt->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <div>{{ $pmt->collectedBy?->name ?? 'Online Gateway' }}</div>
                                @if($pmt->transaction_reference)
                                    <div class="text-[10px] font-mono text-slate-400">Ref: {{ $pmt->transaction_reference }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                No payment receipts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
