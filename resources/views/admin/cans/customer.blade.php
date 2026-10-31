@extends('layouts.admin')

@section('title', 'Containers - ' . $customer->user?->name)

@section('page_title', 'Customer Container Fleet: ' . ($customer->user?->name ?? 'Account'))

@section('page_actions')
<a href="{{ route('admin.cans.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    &larr; Back to Container Fleet
</a>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    returnModalOpen: false,
    penaltyModalOpen: false,
    reconcileModalOpen: false,
    selectedCanTypeId: '{{ $canTypes->first()?->id }}'
}">
    <!-- Customer Profile & Container Balances Summary Banner -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Info Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Customer Premises</span>
                <h2 class="text-xl font-black text-slate-900 mt-0.5">{{ $customer->user?->name }}</h2>
                <p class="text-xs text-slate-500">{{ $customer->user?->mobile }} &bull; {{ $customer->customer_type }}</p>
            </div>

            <div class="pt-3 border-t border-slate-100 text-xs space-y-2 text-slate-600">
                <div class="flex justify-between">
                    <span>Branch Hub:</span>
                    <span class="font-bold text-slate-900">{{ $customer->branch?->name ?? 'Headquarters' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Delivery Address:</span>
                    <span class="font-medium text-slate-700 text-right max-w-[200px] truncate" title="{{ $customer->defaultAddress?->formatted_address }}">
                        {{ $customer->defaultAddress?->formatted_address ?? 'Not configured' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Financial Due:</span>
                    <a href="{{ route('admin.accounts.customer', $customer->id) }}" class="font-bold text-brand-600 hover:underline">
                        ₹{{ number_format($customer->outstanding_financial_balance, 2) }} &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Live Container Holding Card -->
        <div class="bg-gradient-to-br from-cyan-600 to-blue-700 text-white p-6 rounded-3xl shadow-xl flex flex-col justify-between">
            <div>
                <span class="text-xs uppercase tracking-wider text-cyan-100 font-bold block">Current Containers in Possession</span>
                <p class="text-4xl font-black mt-2 tracking-tight">
                    {{ $totalHeld }} <span class="text-sm font-sans font-semibold text-cyan-200">Bottles</span>
                </p>
                <p class="text-xs text-cyan-100 mt-1">
                    1:1 bottle exchange model active for this account
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="grid grid-cols-3 gap-2 mt-6">
                <button type="button" @click="returnModalOpen = true" class="flex items-center justify-center gap-1 py-2 px-2 rounded-xl bg-white text-cyan-900 font-bold text-xs shadow-md hover:bg-cyan-50 transition-all">
                    Collect Empty
                </button>

                <button type="button" @click="penaltyModalOpen = true" class="flex items-center justify-center gap-1 py-2 px-2 rounded-xl bg-rose-600 text-white font-bold text-xs shadow-md hover:bg-rose-500 transition-all">
                    Missing Penalty
                </button>

                <button type="button" @click="reconcileModalOpen = true" class="flex items-center justify-center gap-1 py-2 px-2 rounded-xl bg-cyan-900/60 text-white font-bold text-xs shadow-md hover:bg-cyan-900 transition-all">
                    Reconcile
                </button>
            </div>
        </div>

        <!-- Breakdown By Can Type -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Holdings by Can Capacity</span>
            <div class="space-y-2.5 my-2 text-xs">
                @foreach($balances as $bal)
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-700 font-medium">{{ $bal->can_type->name }}:</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            {{ $bal->held_cans_count }} <span class="text-[11px] font-sans font-normal text-slate-400">cans</span>
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="text-[11px] text-slate-400 italic">
                * Replacement valuation: standard missing bottle fees are defined per can type.
            </div>
        </div>
    </div>

    <!-- Container Physical Movement Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Container Physical Movement Passbook</h3>
                <p class="text-xs text-slate-500">Immutable ledger of full deliveries, empty collections, penalties, and reconciliations</p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Statement
            </button>
        </div>

        @if($movements->isEmpty())
            <div class="py-12 text-center text-slate-400 text-xs">
                No container physical movements logged yet for this customer account.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Date & Time</th>
                            <th class="py-3 px-3">Can Type</th>
                            <th class="py-3 px-3">Movement Type</th>
                            <th class="py-3 px-3">Remarks / Order</th>
                            <th class="py-3 px-3 text-right">Delivered Full (+)</th>
                            <th class="py-3 px-3 text-right">Collected Empty (-)</th>
                            <th class="py-3 px-3 text-right">Held Balance</th>
                            <th class="py-3 px-3">Logged By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-slate-800">
                        @foreach($movements as $m)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-3 font-sans text-slate-600 whitespace-nowrap">
                                    {{ $m->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 px-3 font-sans font-bold text-slate-900 whitespace-nowrap">
                                    {{ $m->canType?->name ?? 'Standard Can' }}
                                </td>
                                <td class="py-3 px-3 font-sans whitespace-nowrap">
                                    @if($m->movement_type === 'delivery_exchange')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-50 text-brand-700 border border-brand-200">
                                            Delivery Exchange
                                        </span>
                                    @elseif($m->movement_type === 'return_collection')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Empty Return
                                        </span>
                                    @elseif($m->movement_type === 'missing_penalty')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                            Missing Penalty
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                            Audit Reconciliation
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-sans text-slate-700">
                                    <div>{{ $m->notes }}</div>
                                    @if($m->reference_type === \App\Models\Order::class && $m->reference_id)
                                        <a href="{{ route('admin.orders.show', $m->reference_id) }}" class="text-[11px] font-bold text-brand-600 hover:underline">
                                            View Order &rarr;
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-bold {{ $m->cans_delivered_full > 0 ? 'text-brand-600' : 'text-slate-300' }}">
                                    {{ $m->cans_delivered_full > 0 ? '+' . $m->cans_delivered_full : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold {{ $m->cans_collected_empty > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                                    {{ $m->cans_collected_empty > 0 ? '-' . $m->cans_collected_empty : '—' }}
                                </td>
                                <td class="py-3 px-3 text-right font-black text-slate-900 text-sm">
                                    {{ $m->balance_after_movement }} cans
                                </td>
                                <td class="py-3 px-3 font-sans text-slate-500 whitespace-nowrap">
                                    {{ $m->recordedBy?->name ?? 'System' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: Collect Empty Return -->
    <div x-show="returnModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="returnModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Collect Empty Containers</h3>
                <button type="button" @click="returnModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.cans.return', $customer->id) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Can Type *</label>
                    <select name="can_type_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                        @foreach($canTypes as $ct)
                            <option value="{{ $ct->id }}">{{ $ct->name }} ({{ $ct->capacity_litres }}L)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Empty Cans Collected (Units) *</label>
                    <input type="number" min="1" name="collected_empty" value="1" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Notes / Pickup Remarks</label>
                    <input type="text" name="notes" placeholder="e.g. Extra empties returned from premises" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="returnModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold shadow-md transition-colors">
                        Save Return & Reduce Holding
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Charge Missing Can Penalty -->
    <div x-show="penaltyModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="penaltyModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Charge Missing / Damaged Penalty</h3>
                <button type="button" @click="penaltyModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <p class="text-[11px] text-slate-500">
                This will reduce the customer's container holding and automatically post a monetary debit fee to their financial ledger.
            </p>

            <form method="POST" action="{{ route('admin.cans.penalty', $customer->id) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Can Type *</label>
                    <select name="can_type_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                        @foreach($canTypes as $ct)
                            <option value="{{ $ct->id }}">{{ $ct->name }} (Penalty: ₹{{ number_format($ct->deposit_amount, 2) }}/can)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Missing / Damaged Cans *</label>
                    <input type="number" min="1" name="missing_cans" value="1" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Audit Reason / Justification *</label>
                    <textarea name="reason" rows="3" required placeholder="e.g. Lost 2 cans during office relocation; broken neck seal..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="penaltyModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-md transition-colors">
                        Charge Penalty Fee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Audit Reconciliation -->
    <div x-show="reconcileModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="reconcileModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Physical Count Reconciliation</h3>
                <button type="button" @click="reconcileModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.cans.reconcile', $customer->id) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Can Type *</label>
                    <select name="can_type_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                        @foreach($canTypes as $ct)
                            <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Actual Verified Physical Count *</label>
                    <input type="number" min="0" name="actual_count" required placeholder="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Audit Notes / Explanation *</label>
                    <textarea name="notes" rows="3" required placeholder="e.g. Physical inventory verified at customer site by driver on weekly inspection" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="reconcileModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-bold shadow-md transition-colors">
                        Save Reconciliation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
