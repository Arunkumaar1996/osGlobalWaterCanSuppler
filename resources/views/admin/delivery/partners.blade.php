@extends('layouts.admin')

@section('title', '3rd-Party Delivery Partners')

@section('page_title', 'Outsourced Delivery Partners & Logistics Vendors')

@section('page_actions')
<a href="{{ route('admin.delivery.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
    &larr; Back to Fleet
</a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ partnerModalOpen: false }">
    <!-- Notice & Add Partner Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Third-Party Logistics Fleet</h3>
            <p class="text-xs text-slate-500 max-w-xl mt-1">
                Manage external delivery partner services (Porter, Shadowfax, Dunzo, or local logistics contractors).
                Separate from water suppliers in accordance with business separation rules.
            </p>
        </div>

        <button type="button" @click="partnerModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Register Delivery Partner
        </button>
    </div>

    <!-- Partners Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Partner Name</th>
                        <th class="py-3 px-4">Contact Person</th>
                        <th class="py-3 px-4">Phone & Email</th>
                        <th class="py-3 px-4">Billing Model</th>
                        <th class="py-3 px-4 text-right">Agreed Rate</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($partners as $p)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900 text-sm">
                                {{ $p->name }}
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $p->contact_person }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                <div>{{ $p->contact_phone }}</div>
                                <div class="text-[11px] text-slate-400">{{ $p->contact_email ?? 'No email' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $p->billing_type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 text-sm">
                                ₹{{ number_format($p->rate_per_unit, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $p->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $p->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                No third-party delivery partners registered yet. Click "Register Delivery Partner" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $partners->links() }}
        </div>
    </div>

    <!-- MODAL: Register New Delivery Partner -->
    <div x-show="partnerModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="partnerModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Register Delivery Partner</h3>
                <button type="button" @click="partnerModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.delivery.partners.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Company / Partner Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Porter Express Logistics" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Contact Person *</label>
                        <input type="text" name="contact_person" required placeholder="Manager Name" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number *</label>
                        <input type="text" name="contact_phone" required placeholder="e.g. 9876543210" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" name="contact_email" placeholder="ops@partner.com" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Billing Model *</label>
                        <select name="billing_type" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500">
                            <option value="per_order">Per Order (Flat)</option>
                            <option value="per_can">Per Can Delivered</option>
                            <option value="per_km">Per Kilometer</option>
                            <option value="monthly_fixed">Monthly Retainer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Rate per Unit (₹) *</label>
                        <input type="number" step="0.01" min="0" name="rate_per_unit" value="35.00" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Notes / SLA Terms</label>
                    <input type="text" name="notes" placeholder="e.g. 2 hour delivery SLA in central zones" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="partnerModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold shadow-md transition-colors">
                        Save Partner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
