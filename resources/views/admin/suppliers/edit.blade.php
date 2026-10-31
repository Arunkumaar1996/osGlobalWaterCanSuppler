@extends('layouts.admin')

@section('title', 'Edit ' . $supplier->company_name . ' - Admin')
@section('page_title', 'Edit Supplier: ' . $supplier->company_name)
@section('page_subtitle', 'Update vendor contacts, tax registrations, and banking details')

@section('header_actions')
<a href="{{ route('admin.suppliers.show', $supplier) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition-colors">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    <span>Back to Profile</span>
</a>
@endsection

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Company & Contact Info</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('company_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Primary Contact Person <span class="text-rose-500">*</span></label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('contact_person') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Phone Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">GSTIN Number</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number', $supplier->gst_number) }}" class="w-full px-4 py-2.5 text-sm uppercase font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">PAN Number</label>
                    <input type="text" name="pan_number" value="{{ old('pan_number', $supplier->pan_number) }}" class="w-full px-4 py-2.5 text-sm uppercase font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Address & Facility Location</h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Facility / Office Address</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('address', $supplier->address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">City</label>
                    <input type="text" name="city" value="{{ old('city', $supplier->city) }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">State</label>
                    <input type="text" name="state" value="{{ old('state', $supplier->state) }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pincode</label>
                    <input type="text" name="pincode" value="{{ old('pincode', $supplier->pincode) }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Bank Details (For Payable Settlements)</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $supplier->bank_name) }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Account Number</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $supplier->bank_account_no) }}" class="w-full px-4 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">IFSC Code</label>
                    <input type="text" name="bank_ifsc_code" value="{{ old('bank_ifsc_code', $supplier->bank_ifsc_code) }}" class="w-full px-4 py-2.5 text-sm uppercase font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Internal Notes / Payment Terms</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('notes', $supplier->notes) }}</textarea>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                    <div>
                        <span class="text-xs font-bold text-slate-700 block">Active Supplier</span>
                        <span class="text-[11px] text-slate-400">Can be selected for purchase orders and sourcing</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.suppliers.show', $supplier) }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-200 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700 shadow-sm transition-colors">
                Update Supplier
            </button>
        </div>
    </form>
</div>
@endsection
