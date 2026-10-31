@extends('layouts.admin')

@section('title', 'Ledger - ' . $customer->user?->name)

@section('page_title', 'Customer Ledger: ' . ($customer->user?->name ?? 'Account'))

@section('page_actions')
<a href="{{ route('admin.accounts.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    &larr; Back to Accounts
</a>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    collectModalOpen: false,
    adjustModalOpen: false,
    selectedOrderId: ''
}">
    <!-- Customer Profile Summary & Quick Stats Banner -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Info Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Customer Profile</span>
                    <h2 class="text-xl font-black text-slate-900 mt-0.5">{{ $customer->user?->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $customer->user?->mobile }} &bull; {{ $customer->user?->email }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $customer->allow_pay_later ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                    {{ $customer->allow_pay_later ? 'Credit Approved' : 'Prepaid Only' }}
                </span>
            </div>

            <div class="pt-3 border-t border-slate-100 text-xs space-y-2 text-slate-600">
                <div class="flex justify-between">
                    <span>Branch Hub:</span>
                    <span class="font-bold text-slate-900">{{ $customer->branch?->name ?? 'Headquarters' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Approved Credit Limit:</span>
                    <span class="font-bold font-mono text-slate-900">₹{{ number_format($customer->credit_limit, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Default Address:</span>
                    <span class="font-medium text-slate-700 text-right max-w-[200px] truncate" title="{{ $customer->defaultAddress?->formatted_address }}">
                        {{ $customer->defaultAddress?->formatted_address ?? 'Not configured' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Live Financial Due Card -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-6 rounded-3xl shadow-xl flex flex-col justify-between">
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 font-bold block">Current Financial Due</span>
                <p class="text-3xl font-black mt-2 tracking-tight {{ $customer->outstanding_financial_balance > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    ₹{{ number_format($customer->outstanding_financial_balance, 2) }}
                </p>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $customer->outstanding_financial_balance > 0 ? 'Customer has unpaid balance' : 'Zero outstanding dues' }}
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="grid grid-cols-2 gap-2 mt-6">
                <button type="button" @click="collectModalOpen = true; selectedOrderId = ''" class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Record Collection
                </button>

                <button type="button" @click="adjustModalOpen = true" class="flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Manual Adjustment
                </button>
            </div>
        </div>

        <!-- Lifetime Turnout Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Accounts Summary</span>
            <div class="space-y-3 my-2 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-600">Total Billed (Debits):</span>
                    <span class="font-mono font-bold text-rose-600 text-sm">₹{{ number_format($customer->ledger()->sum('debit_amount'), 2) }}</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-600">Total Received (Credits):</span>
                    <span class="font-mono font-bold text-emerald-600 text-sm">₹{{ number_format($customer->ledger()->sum('credit_amount'), 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Total Receipts Issued:</span>
                    <span class="font-bold text-slate-800">{{ $customer->payments()->count() }} Receipts</span>
                </div>
            </div>
            <div class="text-[11px] text-slate-400 italic">
                * Segregation Rule: Can balances are tracked on container physical ledger.
            </div>
        </div>
    </div>

    <!-- Pending / Unpaid Invoices Section -->
    @if($unpaidOrders->isNotEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pending Invoices / Unsettled Orders</h3>
                    <p class="text-xs text-slate-500">Orders placed on Pay Later credit or awaiting payment</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    {{ $unpaidOrders->count() }} Orders Pending Payment
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-2.5 px-3">Order #</th>
                            <th class="py-2.5 px-3">Delivery Date</th>
                            <th class="py-2.5 px-3">Payment Status</th>
                            <th class="py-2.5 px-3 text-right">Order Total</th>
                            <th class="py-2.5 px-3 text-right">Paid</th>
                            <th class="py-2.5 px-3 text-right">Remaining Due</th>
                            <th class="py-2.5 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($unpaidOrders as $ord)
                            @php
                                $due = max(0, (float) $ord->total_amount - (float) $ord->paid_amount);
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-3 font-bold text-slate-900">
                                    <a href="{{ route('admin.orders.show', $ord->id) }}" class="text-brand-600 hover:underline">
                                        {{ $ord->order_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 text-slate-600">
                                    {{ $ord->delivery_date->format('d M Y') }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $ord->payment_status === 'partially_paid' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ str_replace('_', ' ', $ord->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono text-slate-800">
                                    ₹{{ number_format($ord->total_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono text-emerald-600">
                                    ₹{{ number_format($ord->paid_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600">
                                    ₹{{ number_format($due, 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <button type="button" @click="collectModalOpen = true; selectedOrderId = '{{ $ord->id }}'; document.getElementById('collect_amount').value = '{{ $due }}'" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm transition-colors">
                                        Collect ₹{{ number_format($due, 2) }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Double-Entry Transaction Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Audit Ledger (Double-Entry Passbook)</h3>
                <p class="text-xs text-slate-500">Immutable ledger entries with running balance verification</p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Statement
            </button>
        </div>

        @if($entries->isEmpty())
            <div class="py-12 text-center text-slate-400 text-xs">
                No ledger transactions recorded yet for this customer account.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Date & Time</th>
                            <th class="py-3 px-3">Transaction Type</th>
                            <th class="py-3 px-3">Notes & Reference</th>
                            <th class="py-3 px-3 text-right">Debit (+)</th>
                            <th class="py-3 px-3 text-right">Credit (-)</th>
                            <th class="py-3 px-3 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-slate-800">
                        @foreach($entries as $entry)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-3 font-sans text-slate-600 whitespace-nowrap">
                                    {{ $entry->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 px-3 font-sans whitespace-nowrap">
                                    @if($entry->transaction_type === 'order_charge')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                                            Order Charge
                                        </span>
                                    @elseif($entry->transaction_type === 'payment_received')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Payment Received
                                        </span>
                                    @elseif($entry->transaction_type === 'credit_adjustment')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-sky-50 text-sky-800 border border-sky-200">
                                            Credit Adjustment
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-50 text-purple-800 border border-purple-200">
                                            Debit Adjustment
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-sans text-slate-700">
                                    <div>{{ $entry->notes }}</div>
                                    @if($entry->reference_type === \App\Models\Order::class && $entry->reference_id)
                                        <a href="{{ route('admin.orders.show', $entry->reference_id) }}" class="text-[11px] font-bold text-brand-600 hover:underline">
                                            View Order &rarr;
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-bold {{ $entry->debit_amount > 0 ? 'text-rose-600' : 'text-slate-300' }}">
                                    {{ $entry->debit_amount > 0 ? '₹' . number_format($entry->debit_amount, 2) : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold {{ $entry->credit_amount > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                                    {{ $entry->credit_amount > 0 ? '₹' . number_format($entry->credit_amount, 2) : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-black text-slate-900">
                                    ₹{{ number_format($entry->running_balance, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: Record Collection -->
    <div x-show="collectModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="collectModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Record Payment Collection</h3>
                <button type="button" @click="collectModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.accounts.collect', $customer->id) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Amount to Collect (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="collect_amount" required value="{{ $customer->outstanding_financial_balance > 0 ? $customer->outstanding_financial_balance : '' }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                        <option value="cash">Cash Collection</option>
                        <option value="upi">UPI / QR Code</option>
                        <option value="bank_transfer">Bank Transfer (NEFT / IMPS)</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Apply to Specific Order (Optional)</label>
                    <select name="order_id" x-model="selectedOrderId" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-brand-500">
                        <option value="">-- Apply to general account dues balance --</option>
                        @foreach($unpaidOrders as $uOrd)
                            <option value="{{ $uOrd->id }}">{{ $uOrd->order_number }} (Due: ₹{{ number_format($uOrd->total_amount - $uOrd->paid_amount, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Transaction Ref / Cheque No.</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. UPI Ref / Bank UTR / Cheque #" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Notes / Remarks</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Handed over at store / office collection..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="collectModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md transition-colors">
                        Save Payment & Credit Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Balance Adjustment -->
    <div x-show="adjustModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="adjustModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Manual Balance Adjustment</h3>
                <button type="button" @click="adjustModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.accounts.adjust', $customer->id) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Adjustment Type *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="adjustment_type" value="credit" checked class="text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="font-bold text-emerald-700 block">Credit Waiver</span>
                                <span class="text-[10px] text-slate-500">Reduces customer due</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="adjustment_type" value="debit" class="text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="font-bold text-rose-700 block">Debit Charge</span>
                                <span class="text-[10px] text-slate-500">Increases customer due</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Amount (₹) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Audit Reason / Justification *</label>
                    <textarea name="reason" rows="3" required placeholder="Detailed reason for credit waiver or debit adjustment (e.g. Promotional loyalty credit, dispute settlement, damaged can penalty)..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-bold shadow-md transition-colors">
                        Post Audit Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
