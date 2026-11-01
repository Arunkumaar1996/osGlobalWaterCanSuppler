@extends('layouts.customer')

@section('title', 'Order Checkout')

@section('content')
<div class="max-w-5xl mx-auto w-full pb-36 lg:pb-16" x-data="checkoutEngine()">
    <!-- Checkout Header -->
    <div class="pb-6 border-b border-slate-200 mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Order Checkout</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Select delivery location, slot, exchange option, and payment method</p>
        </div>
        <a href="{{ route('cart.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
            <span>&larr; Back to Cart</span>
        </a>
    </div>

    @if($errors->has('checkout'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold flex items-center justify-between">
            <span>{{ $errors->first('checkout') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
        </div>
    @endif

    <!-- Standalone Modal/Panel for Adding New Address (Outside the main checkout form to avoid nested forms) -->
    <div x-show="showNewAddressModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Add New Delivery Address</h3>
                <button type="button" @click="showNewAddressModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>
            <p class="text-xs text-slate-500">Serviceable Chennai pincodes: <strong>600032, 600015, 600017, 600035, 600020, 600041, 600042, 600096</strong></p>

            <form method="POST" action="{{ route('checkout.address.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Contact Person *</label>
                        <input type="text" name="contact_person_name" value="{{ old('contact_person_name', auth()->user()->name) }}" required class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mobile (10 digits) *</label>
                        <input type="text" name="contact_person_mobile" value="{{ old('contact_person_mobile', auth()->user()->mobile) }}" required maxlength="10" class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Address Label *</label>
                        <select name="address_type" class="w-full text-xs rounded-xl border-slate-300">
                            <option value="home">Home / Residence</option>
                            <option value="office">Office / Commercial</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">House / Flat / Door No. *</label>
                        <input type="text" name="house_flat_no" placeholder="e.g. Flat 3B" required class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Building / Apartment / Society Name</label>
                        <input type="text" name="building_apartment_name" placeholder="e.g. Olympia Opaline" class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Street Address / Road *</label>
                        <input type="text" name="street_address" placeholder="e.g. 14, 2nd Main Road" required class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Area / Locality *</label>
                        <input type="text" name="area" placeholder="e.g. Guindy" required class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pincode (6 digits) *</label>
                        <input type="text" name="pincode" placeholder="e.g. 600032" maxlength="6" required class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">City *</label>
                        <input type="text" name="city" value="Chennai" required class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">State *</label>
                        <input type="text" name="state" value="Tamil Nadu" required class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Driver Delivery Instructions (Optional)</label>
                        <input type="text" name="delivery_instructions" placeholder="e.g. 2nd floor, lift available" class="w-full text-xs rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <label class="flex items-center gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="is_default" value="1" checked class="rounded text-brand-600">
                        <span>Set as default address</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showNewAddressModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md transition-colors">
                            Save Address
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Checkout Form -->
    <form id="checkoutForm" method="POST" action="{{ route('checkout.process') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ 'IDEMP_' . auth()->id() . '_' . time() . '_' . rand(1000, 9999) }}">

        <!-- Left Step Blocks (Col 1 to 8) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Step 1. Select Delivery Address -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs">1</span>
                        Delivery Address
                    </h3>
                    <button type="button" @click="showNewAddressModal = true" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                        + Add New Address
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($addresses as $addr)
                        <label class="block p-4 rounded-2xl border cursor-pointer transition-all"
                            :class="selectedAddress == {{ $addr->id }} ? 'border-brand-500 bg-brand-50/30 ring-1 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="delivery_address_id" value="{{ $addr->id }}"
                                    x-model="selectedAddress"
                                    class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300"
                                    {{ $addr->is_default ? 'checked' : '' }}>
                                <div class="flex-1 text-xs">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-slate-900 text-sm">{{ $addr->contact_person_name }}</span>
                                        <span class="text-slate-500">({{ $addr->contact_person_mobile }})</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">{{ $addr->address_type }}</span>
                                        @if($addr->is_default)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-100 text-brand-700">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-slate-600 leading-relaxed">{{ $addr->formatted_address }}</p>
                                    @if($addr->delivery_instructions)
                                        <p class="text-slate-400 mt-1 italic">Note: {{ $addr->delivery_instructions }}</p>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="p-4 rounded-2xl bg-amber-50 text-amber-800 text-xs font-semibold flex items-center justify-between">
                            <span>No saved delivery addresses found. Please add an address to continue.</span>
                            <button type="button" @click="showNewAddressModal = true" class="px-3 py-1.5 bg-amber-600 text-white font-bold rounded-lg text-xs">Add Address</button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Step 2. Water Can Exchange Option for Existing vs New Customers -->
            @if($cart->hasExchangeableCans())
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs">2</span>
                                Water Can Exchange Option
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Please indicate if you have empty water cans ready for return</p>
                        </div>
                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-1 rounded-lg border border-brand-200">
                            {{ $exchangeableCount }} Exchangeable Can(s)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Option 1: Existing Customer / Has Empty Cans to Return -->
                        <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-start gap-3"
                            :class="hasEmptyCans ? 'border-brand-500 bg-brand-50/40 ring-1 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="has_empty_cans_to_exchange" value="1" x-model="hasEmptyCans" class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300">
                            <div class="text-xs">
                                <span class="font-bold text-slate-900 text-sm block">Yes, I have empty cans (1:1 Equal Exchange)</span>
                                <span class="text-slate-500 block mt-1 leading-normal">
                                    I will hand over {{ $exchangeableCount }} matching empty cans to the driver in exchange for {{ $exchangeableCount }} refilled cans.
                                </span>
                                <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Can Deposit: ₹0 (Water Only &bull; 1:1 Equal Exchange)
                                </span>
                            </div>
                        </label>

                        <!-- Option 2: New Customer / No Empty Cans -->
                        <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-start gap-3"
                            :class="!hasEmptyCans ? 'border-brand-500 bg-brand-50/40 ring-1 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="has_empty_cans_to_exchange" value="0" x-model="hasEmptyCans" class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300">
                            <div class="text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900 text-sm block">No, I'm a new customer</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-100 text-brand-700">New User</span>
                                </div>
                                @if(!empty($chargeNewCustomerDeposit))
                                    <span class="text-slate-500 block mt-1 leading-normal">
                                        Don't have empty cans yet? A refundable bottle deposit of ₹{{ number_format($depositPerCan ?? 150, 2) }}/can is added. 100% refundable upon return.
                                    </span>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Refundable Deposit: +₹{{ number_format($exchangeableCount * ($depositPerCan ?? 150), 2) }}
                                    </span>
                                @else
                                    <span class="text-slate-500 block mt-1 leading-normal">
                                        Don't have empty cans? Zero bottle deposit collected! As a new customer, you pay <strong>ONLY for the water refill</strong> (0 empty cans collected).
                                    </span>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Deposit Waived: ₹0.00 (Water Refill Only)
                                    </span>
                                @endif
                            </div>
                        </label>
                    </div>
                </div>
            @endif

            <!-- Step 3. Select Delivery Date & Time Window -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs">3</span>
                    Delivery Date & Slot
                </h3>

                <!-- Date Picker Pills -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Delivery Date</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach($deliveryDates as $d)
                            <button type="button" @click="changeDate('{{ $d['date'] }}')"
                                class="p-2.5 rounded-xl border text-xs font-bold transition-all text-center"
                                :class="selectedDate === '{{ $d['date'] }}' ? 'border-brand-600 bg-brand-600 text-white shadow-sm' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                {{ $d['label'] }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="delivery_date" :value="selectedDate">
                </div>

                <!-- Available Time Slots -->
                <div class="pt-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Available Time Windows</label>
                    <div x-show="loadingSlots" class="text-xs text-slate-500 py-4 text-center">Checking slot capacity...</div>

                    <div x-show="!loadingSlots" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="slot in availableSlots" :key="slot.id">
                            <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-3"
                                :class="selectedSlot == slot.id ? 'border-brand-500 bg-brand-50/40 ring-1 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="delivery_slot_id" :value="slot.id" x-model="selectedSlot" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 block" x-text="slot.name"></span>
                                    <span class="text-slate-500" x-text="slot.time_range"></span>
                                </div>
                            </label>
                        </template>
                    </div>

                    <div x-show="!loadingSlots && availableSlots.length === 0" class="p-4 rounded-xl bg-amber-50 text-amber-800 text-xs font-semibold">
                        ⚠️ No delivery slots are currently available for this date. Please choose another delivery date above.
                    </div>
                </div>

                <!-- Special Delivery Instructions -->
                <div class="pt-3">
                    <label for="customer_notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Driver Instructions for this Order (Optional)
                    </label>
                    <textarea id="customer_notes" name="customer_notes" rows="2"
                        placeholder="e.g. Ring the bell twice. If not available, please leave at flat door."
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                </div>
            </div>

            <!-- Step 4. Payment Method Selection -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs">4</span>
                    Payment Method
                </h3>

                <div class="space-y-3">
                    <!-- Cash on Delivery -->
                    <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-start gap-3"
                        :class="paymentMethod === 'cod' ? 'border-brand-500 bg-brand-50/40 ring-1 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300" checked>
                        <div class="text-xs">
                            <span class="font-bold text-slate-900 text-sm block">Cash on Delivery (COD)</span>
                            <span class="text-slate-500">Pay cash or scan driver's UPI QR code upon doorstep delivery.</span>
                        </div>
                    </label>

                    <!-- Pay Later (Customer Credit) -->
                    <label class="p-4 rounded-2xl border transition-all flex items-start gap-3"
                        :class="!allowPayLater ? 'opacity-60 bg-slate-50 cursor-not-allowed border-slate-200' : (paymentMethod === 'pay_later' ? 'border-brand-500 bg-brand-50/40 ring-1 ring-brand-500 cursor-pointer' : 'border-slate-200 hover:border-slate-300 cursor-pointer')">
                        <input type="radio" name="payment_method" value="pay_later" x-model="paymentMethod" :disabled="!allowPayLater" class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300">
                        <div class="text-xs flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">Pay Later (Customer Credit)</span>
                                @if($allowPayLater)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">Eligible</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">Not Available</span>
                                @endif
                            </div>
                            <span class="text-slate-500 block mt-0.5">Order now without immediate payment and settle your weekly or monthly ledger.</span>
                            @if(!$allowPayLater)
                                <span class="text-[11px] text-rose-600 font-semibold block mt-1">
                                    {{ (auth()->user()->customerProfile?->allow_pay_later) ? 'Exceeds current approved credit limit of ₹' . number_format(auth()->user()->customerProfile?->credit_limit, 2) : 'Credit terms require business admin approval.' }}
                                </span>
                            @endif
                        </div>
                    </label>

                    <!-- Razorpay Online Payment -->
                    <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-start gap-3"
                        :class="paymentMethod === 'razorpay' ? 'border-brand-500 bg-brand-50/40 ring-1 ring-brand-500' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="payment_method" value="razorpay" x-model="paymentMethod" class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300">
                        <div class="text-xs">
                            <span class="font-bold text-slate-900 text-sm block">Razorpay Online Payment</span>
                            <span class="text-slate-500">Pay securely using UPI (Google Pay, PhonePe), Credit/Debit Card, or Netbanking.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Right Order Summary Column (Col 9 to 12) -->
        <div class="lg:col-span-4">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4 sticky top-24">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                    Order Review
                </h3>

                <!-- Items Snapshot List -->
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @foreach($cart->items as $item)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50">
                            <div>
                                <span class="font-bold text-slate-800">{{ $item->product->name }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $item->quantity }} &times; ₹{{ number_format($item->product->selling_price, 2) }}</span>
                            </div>
                            <span class="font-bold text-slate-900">₹{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Calculation -->
                <div class="space-y-2 text-xs pt-2">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-slate-900">₹{{ number_format($subtotal, 2) }}</span>
                    </div>

                    <!-- Can Deposit Line -->
                    <template x-if="!hasEmptyCans && chargeNewCustomerDeposit && depositPerCan > 0">
                        <div class="flex items-center justify-between text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                            <div>
                                <span class="font-bold block">Can Security Deposit</span>
                                <span class="text-[10px] text-amber-600">{{ $exchangeableCount }} cans &times; ₹{{ number_format($depositPerCan ?? 150, 2) }} (Refundable)</span>
                            </div>
                            <span class="font-black text-xs text-amber-900" x-text="'+₹' + (depositPerCan * {{ $exchangeableCount }}).toFixed(2)"></span>
                        </div>
                    </template>
                    <template x-if="hasEmptyCans || !chargeNewCustomerDeposit || depositPerCan == 0">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Can Deposit</span>
                            <span class="text-emerald-600 font-bold" x-text="hasEmptyCans ? '₹0.00 (1:1 Equal Exchange)' : '₹0.00 (Water Refill Only)'"></span>
                        </div>
                    </template>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Delivery Charge</span>
                        <span class="font-bold {{ $deliveryCharge == 0 ? 'text-emerald-600' : 'text-slate-900' }}">
                            {{ $deliveryCharge == 0 ? 'FREE' : '₹' . number_format($deliveryCharge, 2) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Taxes (GST)</span>
                        <span class="text-slate-500">Included</span>
                    </div>

                    <!-- Previous Outstanding Dues Displayed STRICTLY SEPARATELY -->
                    @if($previousDue > 0)
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-center justify-between">
                            <div>
                                <span class="font-bold block">Previous Outstanding</span>
                                <span class="text-[10px] text-amber-700">From earlier deliveries</span>
                            </div>
                            <span class="font-black text-sm">₹{{ number_format($previousDue, 2) }}</span>
                        </div>
                    @endif
                </div>

                <!-- Total Amount Payable -->
                <div class="pt-4 border-t border-slate-100 flex items-baseline justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 block uppercase tracking-wider">This Order Payable</span>
                        <span class="text-2xl font-black text-slate-900" x-text="'₹' + totalAmount"></span>
                    </div>
                </div>

                <!-- Can Exchange Summary Notice -->
                <div class="p-3 bg-brand-50 border border-brand-200 rounded-xl text-[11px] text-brand-900 leading-tight">
                    <template x-if="hasEmptyCans">
                        <div>
                            <strong>1:1 Equal Can Exchange:</strong> Hand over {{ $exchangeableCount }} matching empty can(s) to our driver in exchange for {{ $exchangeableCount }} refilled cans.
                        </div>
                    </template>
                    <template x-if="!hasEmptyCans && chargeNewCustomerDeposit && depositPerCan > 0">
                        <div>
                            <strong>New Customer Order:</strong> Zero empty cans required. Bottle deposit of ₹{{ number_format($depositPerCan ?? 150, 2) }}/can is 100% refundable upon return.
                        </div>
                    </template>
                    <template x-if="!hasEmptyCans && (!chargeNewCustomerDeposit || depositPerCan == 0)">
                        <div>
                            <strong>New Customer Order:</strong> Zero empty cans required. Bottle deposit is <strong>WAIVED (₹0.00)</strong> — pay strictly for the water refill!
                        </div>
                    </template>
                </div>

                <!-- Submit Button with Safe Accessibility & Clear Visibility -->
                <div class="pt-2">
                    <button type="submit" 
                        :disabled="!selectedAddress || !selectedSlot || availableSlots.length === 0"
                        class="w-full py-4 px-4 rounded-xl text-sm font-extrabold text-white bg-brand-600 hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-xl shadow-brand-500/25 hover-lift transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>Confirm & Place Order</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>

                    <div x-show="availableSlots.length === 0" class="mt-2 text-[11px] text-rose-600 font-semibold text-center">
                        ⚠️ No delivery slots open on this date. Please choose another delivery date above.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function checkoutEngine() {
        return {
            showNewAddressModal: false,
            selectedAddress: {{ $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()?->id ?? 'null' }},
            selectedDate: '{{ $initialDate }}',
            selectedSlot: {{ $slots->first()?->id ?? 'null' }},
            hasEmptyCans: {{ $cart->has_empty_cans_to_exchange ? 'true' : 'false' }},
            paymentMethod: 'cod',
            allowPayLater: {{ $allowPayLater ? 'true' : 'false' }},
            loadingSlots: false,
            availableSlots: @json($slots->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'time_range' => $s->formatted_time_range])),
            subtotal: {{ (float) $subtotal }},
            deliveryCharge: {{ (float) $deliveryCharge }},
            depositPerCan: {{ (float) ($depositPerCan ?? 0.00) }},
            chargeNewCustomerDeposit: {{ !empty($chargeNewCustomerDeposit) ? 'true' : 'false' }},
            exchangeableCount: {{ (int) $exchangeableCount }},

            get totalAmount() {
                let deposit = (!this.hasEmptyCans && this.chargeNewCustomerDeposit) ? (this.depositPerCan * this.exchangeableCount) : 0;
                let total = this.subtotal + this.deliveryCharge + deposit;
                return total.toFixed(2);
            },

            changeDate(date) {
                this.selectedDate = date;
                this.loadingSlots = true;

                fetch('{{ route("checkout.get-slots") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ date: date })
                })
                .then(res => res.json())
                .then(data => {
                    this.loadingSlots = false;
                    this.availableSlots = data.slots;
                    if (this.availableSlots.length > 0) {
                        this.selectedSlot = this.availableSlots[0].id;
                    } else {
                        this.selectedSlot = null;
                    }
                })
                .catch(err => {
                    this.loadingSlots = false;
                });
            }
        };
    }
</script>
@endpush
@endsection
