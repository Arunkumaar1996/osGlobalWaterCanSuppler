@extends('layouts.admin')

@section('title', $supplier->company_name . ' - Supplier Hub')
@section('page_title', $supplier->company_name)
@section('page_subtitle', 'Supplier profile, product sourcing prices, purchase orders, and payable ledger')

@section('header_actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        <span>Edit Supplier</span>
    </a>
    <a href="{{ route('admin.procurement.create', ['supplier_id' => $supplier->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white font-semibold text-sm hover:bg-brand-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Issue Purchase Order</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'products', showPaymentModal: false, showMapProductModal: false }">
    <!-- Top Metrics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Outstanding Payable Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Accounts Payable Balance</span>
            <div class="text-2xl font-black {{ $supplier->outstanding_payable_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                ₹{{ number_format($supplier->outstanding_payable_balance, 2) }}
            </div>
            <div class="mt-3 flex items-center justify-between">
                <span class="text-xs text-slate-500">Current liability</span>
                <button type="button" @click="showPaymentModal = true" class="text-xs font-bold text-brand-600 hover:text-brand-700 underline">
                    Record Payment &rarr;
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Products Mapped</span>
            <div class="text-2xl font-black text-slate-900">{{ $supplier->supplierProducts->count() }}</div>
            <span class="text-xs text-slate-500 mt-3 block">Configured for sourcing</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Purchase Orders</span>
            <div class="text-2xl font-black text-slate-900">{{ $supplier->purchaseOrders->count() }}</div>
            <span class="text-xs text-slate-500 mt-3 block">Total orders issued</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Contact Information</span>
            <div class="font-bold text-sm text-slate-900 truncate">{{ $supplier->contact_person }}</div>
            <div class="text-xs text-slate-500 truncate">{{ $supplier->phone }}</div>
            <div class="text-xs text-slate-400 truncate">{{ $supplier->email ?? 'No email' }}</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 flex gap-6 text-sm font-bold">
        <button type="button" @click="activeTab = 'products'" :class="activeTab === 'products' ? 'text-brand-600 border-b-2 border-brand-600 pb-3' : 'text-slate-500 hover:text-slate-800 pb-3'">
            Mapped Products ({{ $supplier->supplierProducts->count() }})
        </button>
        <button type="button" @click="activeTab = 'pos'" :class="activeTab === 'pos' ? 'text-brand-600 border-b-2 border-brand-600 pb-3' : 'text-slate-500 hover:text-slate-800 pb-3'">
            Recent Purchase Orders ({{ $supplier->purchaseOrders->count() }})
        </button>
        <button type="button" @click="activeTab = 'ledger'" :class="activeTab === 'ledger' ? 'text-brand-600 border-b-2 border-brand-600 pb-3' : 'text-slate-500 hover:text-slate-800 pb-3'">
            Financial Ledger & Statements
        </button>
        <button type="button" @click="activeTab = 'details'" :class="activeTab === 'details' ? 'text-brand-600 border-b-2 border-brand-600 pb-3' : 'text-slate-500 hover:text-slate-800 pb-3'">
            Company & Banking Info
        </button>
    </div>

    <!-- Tab 1: Mapped Products -->
    <div x-show="activeTab === 'products'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Procurement Price List for {{ $supplier->company_name }}</h3>
            <button type="button" @click="showMapProductModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-50 text-brand-700 text-xs font-bold rounded-xl hover:bg-brand-100 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Map Product</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Our Product</th>
                            <th class="px-6 py-3.5">Supplier SKU</th>
                            <th class="px-6 py-3.5">Purchase Cost Price</th>
                            <th class="px-6 py-3.5">Lead Time</th>
                            <th class="px-6 py-3.5">Preference</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($supplier->supplierProducts as $sp)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $sp->product?->name }}</div>
                                <div class="text-xs text-slate-400">SKU: {{ $sp->product?->sku }} ({{ $sp->product?->capacity_value }} {{ $sp->product?->capacity_unit }})</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700">
                                {{ $sp->supplier_sku ?? '—' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                ₹{{ number_format($sp->purchase_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                {{ $sp->lead_time_hours }} hours
                            </td>
                            <td class="px-6 py-4">
                                @if($sp->is_preferred)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                        Preferred Supplier
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Secondary</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('admin.suppliers.products.destroy', [$supplier, $sp]) }}" onsubmit="return confirm('Remove product mapping?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-semibold">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">
                                No products mapped yet. Click "Map Product" to configure purchase pricing.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Purchase Orders -->
    <div x-show="activeTab === 'pos'" class="space-y-4" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">PO Number</th>
                            <th class="px-6 py-3.5">Created Date</th>
                            <th class="px-6 py-3.5">Expected Delivery</th>
                            <th class="px-6 py-3.5">Total Amount</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($supplier->purchaseOrders as $po)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                {{ $po->po_number }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $po->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                {{ $po->expected_delivery_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                ₹{{ number_format($po->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold 
                                    {{ $po->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $po->status === 'partially_received' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $po->status === 'issued' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                    {{ $po->status === 'cancelled' ? 'bg-slate-100 text-slate-600' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.procurement.show', $po) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                                    View PO &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">
                                No purchase orders issued to this supplier yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Financial Ledger -->
    <div x-show="activeTab === 'ledger'" class="space-y-4" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Payable Accounts Ledger Statement</h3>
                    <p class="text-xs text-slate-500">Credits increase payable liability (received goods); Debits reduce balance (settled payments)</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400">Current Balance:</span>
                    <span class="text-sm font-bold ml-1 {{ $supplier->outstanding_payable_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        ₹{{ number_format($supplier->outstanding_payable_balance, 2) }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5">Transaction Type</th>
                            <th class="px-6 py-3.5">Notes / Description</th>
                            <th class="px-6 py-3.5 text-right">Debit (₹)</th>
                            <th class="px-6 py-3.5 text-right">Credit (₹)</th>
                            <th class="px-6 py-3.5 text-right">Running Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @forelse($supplier->ledger as $entry)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-3.5 text-slate-600 font-sans">
                                {{ $entry->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-6 py-3.5 font-sans font-bold {{ $entry->debit_amount > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $entry->transaction_type)) }}
                            </td>
                            <td class="px-6 py-3.5 font-sans text-slate-600">
                                {{ $entry->notes }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-emerald-600">
                                {{ $entry->debit_amount > 0 ? number_format($entry->debit_amount, 2) : '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-rose-600">
                                {{ $entry->credit_amount > 0 ? number_format($entry->credit_amount, 2) : '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900">
                                ₹{{ number_format($entry->running_balance, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-sans text-xs">
                                No financial ledger entries recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 4: Company & Banking Details -->
    <div x-show="activeTab === 'details'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Business Information</h4>
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">GSTIN</span>
                        <span class="font-bold text-slate-900">{{ $supplier->gst_number ?? 'Not Registered' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">PAN</span>
                        <span class="font-bold text-slate-900">{{ $supplier->pan_number ?? '—' }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-slate-400 block">Plant Address</span>
                        <span class="font-semibold text-slate-700">{{ $supplier->address ?? '—' }}, {{ $supplier->city }}, {{ $supplier->state }} - {{ $supplier->pincode }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-slate-400 block">Notes & Terms</span>
                        <span class="text-slate-600">{{ $supplier->notes ?? 'Standard commercial terms' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Bank Account for Settlements</h4>
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Bank Name</span>
                        <span class="font-bold text-slate-900">{{ $supplier->bank_name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Account Number</span>
                        <span class="font-mono font-bold text-slate-900">{{ $supplier->bank_account_no ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">IFSC Code</span>
                        <span class="font-mono font-bold text-slate-900">{{ $supplier->bank_ifsc_code ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Record Supplier Payment -->
    <div x-show="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="showPaymentModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 space-y-5" @click.away="showPaymentModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Record Payment to {{ $supplier->company_name }}</h3>
                <button type="button" @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.suppliers.payments.store', $supplier) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Payment Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $supplier->outstanding_payable_balance > 0 ? $supplier->outstanding_payable_balance : '') }}" required min="1" class="w-full px-4 py-2.5 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[11px] text-slate-400 mt-1">Current Payable: ₹{{ number_format($supplier->outstanding_payable_balance, 2) }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Payment Mode <span class="text-rose-500">*</span></label>
                    <select name="payment_mode" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="bank_transfer">Bank Transfer / IMPS</option>
                        <option value="upi">UPI / QR Code</option>
                        <option value="neft_rtgs">NEFT / RTGS</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Transaction Reference / UTR Number</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. UTR1298401928" class="w-full px-4 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Settlement for GRN deliveries" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showPaymentModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl hover:bg-brand-700">
                        Confirm & Post to Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Map Product to Supplier -->
    <div x-show="showMapProductModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;" @keydown.escape.window="showMapProductModal = false">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 space-y-5" @click.away="showMapProductModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Map Product to {{ $supplier->company_name }}</h3>
                <button type="button" @click="showMapProductModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.suppliers.products.store', $supplier) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Product <span class="text-rose-500">*</span></label>
                    <select name="product_id" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Choose Product Catalog Item</option>
                        @foreach($allProducts as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }} - Selling: ₹{{ number_format($p->selling_price, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Supplier SKU / Internal Identifier</label>
                    <input type="text" name="supplier_sku" placeholder="e.g. GLACIER-20L" class="w-full px-4 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Purchase Cost (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="purchase_price" required min="0" placeholder="e.g. 40.00" class="w-full px-4 py-2.5 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lead Time (Hours) <span class="text-rose-500">*</span></label>
                        <input type="number" name="lead_time_hours" value="24" required min="1" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_preferred" value="1" class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Set as Preferred Supplier</span>
                            <span class="text-[11px] text-slate-400">Prioritized for split fulfillment</span>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="showMapProductModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl hover:bg-brand-700">
                        Save Product Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
