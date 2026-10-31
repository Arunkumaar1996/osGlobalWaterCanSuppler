@extends('layouts.guest')

@section('title', 'Register New Customer Account')

@section('content')
<div class="py-10 sm:py-14 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto w-full">
    <div class="text-center mb-8">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Create Your Customer Account</h2>
        <p class="mt-2 text-sm text-slate-600">
            Already have an account? <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-500">Sign in here</a>
        </p>
    </div>

    <!-- Registration Card -->
    <div class="bg-white p-6 sm:p-10 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100">
        <form method="POST" action="{{ route('register.post') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Personal Details -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs">1</span>
                    Personal & Login Credentials
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. Ramesh Kumar"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('name') border-rose-500 @enderror">
                        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label for="mobile" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Number (10 digits) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm font-semibold">+91</span>
                            <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}" required maxlength="10"
                                placeholder="9876543210"
                                class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('mobile') border-rose-500 @enderror">
                        </div>
                        @error('mobile') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email (Optional) -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address (Optional)</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="ramesh@gmail.com"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('email') border-rose-500 @enderror">
                        @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password (min 8 chars) *</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('password') border-rose-500 @enderror">
                        @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
            </div>

            <!-- Section 2: Default Delivery Address -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs">2</span>
                    Default Delivery Address
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Flat / House No -->
                    <div>
                        <label for="house_flat_no" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Flat / House / Door No *</label>
                        <input type="text" id="house_flat_no" name="house_flat_no" value="{{ old('house_flat_no') }}" required
                            placeholder="e.g. Flat 4B, 3rd Floor"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('house_flat_no') border-rose-500 @enderror">
                        @error('house_flat_no') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Apartment / Building -->
                    <div>
                        <label for="building_apartment_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Building / Apartment Name</label>
                        <input type="text" id="building_apartment_name" name="building_apartment_name" value="{{ old('building_apartment_name') }}"
                            placeholder="e.g. Green Meadows Enclave"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <!-- Street Address -->
                    <div class="sm:col-span-2">
                        <label for="street_address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Street / Road Address *</label>
                        <input type="text" id="street_address" name="street_address" value="{{ old('street_address') }}" required
                            placeholder="e.g. 5th Main Road, Gandhi Nagar"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('street_address') border-rose-500 @enderror">
                        @error('street_address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Landmark -->
                    <div>
                        <label for="landmark" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Landmark (Optional)</label>
                        <input type="text" id="landmark" name="landmark" value="{{ old('landmark') }}"
                            placeholder="e.g. Opposite Post Office"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <!-- Area -->
                    <div>
                        <label for="area" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Area / Locality *</label>
                        <input type="text" id="area" name="area" value="{{ old('area', 'Guindy') }}" required
                            placeholder="e.g. Guindy"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('area') border-rose-500 @enderror">
                        @error('area') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">City *</label>
                        <input type="text" id="city" name="city" value="{{ old('city', 'Chennai') }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('city') border-rose-500 @enderror">
                    </div>

                    <!-- State -->
                    <div>
                        <label for="state" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">State *</label>
                        <input type="text" id="state" name="state" value="{{ old('state', 'Tamil Nadu') }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('state') border-rose-500 @enderror">
                    </div>

                    <!-- Pincode -->
                    <div>
                        <label for="pincode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pincode (6 digits) *</label>
                        <input type="text" id="pincode" name="pincode" value="{{ old('pincode', '600032') }}" required maxlength="6"
                            placeholder="600032"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('pincode') border-rose-500 @enderror">
                        @error('pincode') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Referral Code -->
                    <div>
                        <label for="referral_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Referral Code (Optional)</label>
                        <input type="text" id="referral_code" name="referral_code" value="{{ old('referral_code') }}"
                            placeholder="e.g. ANAND42"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('referral_code') border-rose-500 @enderror">
                        @error('referral_code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Instructions -->
                    <div class="sm:col-span-2">
                        <label for="delivery_instructions" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Delivery Instructions for Driver</label>
                        <textarea id="delivery_instructions" name="delivery_instructions" rows="2"
                            placeholder="e.g. Ring the bell twice. If unavailable, leave cans with security."
                            class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('delivery_instructions') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/25 hover-lift transition-all">
                Complete Registration & Continue
            </button>
        </form>
    </div>
</div>
@endsection
