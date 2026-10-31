<section class="relative overflow-hidden bg-gradient-to-b from-brand-50/80 via-white to-slate-50 pt-12 pb-16 sm:pt-20 sm:pb-24 border-b border-slate-200/60">
    <!-- Ambient Water Glow Effects -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-tr from-brand-200/40 via-cyan-100/30 to-transparent blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            <!-- Left Copy Column -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <!-- Pill Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-100/80 text-brand-800 border border-brand-200/60 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-brand-600 animate-ping"></span>
                    <span>100% Hygienic Packaged Drinking Water</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.15]">
                    Pure Drinking Water, <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-cyan-500">Delivered to Your Doorstep</span>
                </h1>

                <!-- Supporting Text -->
                <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Order quality drinking water cans in just a few clicks. Choose your preferred delivery time and enjoy reliable doorstep delivery.
                </p>

                <!-- Primary & Secondary CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="#products" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-extrabold text-white bg-brand-600 hover:bg-brand-700 rounded-2xl shadow-xl shadow-brand-500/25 hover-lift transition-all">
                        <span>Order Water Now</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="#how-it-works" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl shadow-sm transition-colors">
                        <span>Explore How It Works</span>
                    </a>
                </div>

                <!-- Interactive Hero Pincode Checker Card -->
                <div class="pt-6 max-w-lg mx-auto lg:mx-0" x-data="pincodeChecker()">
                    <div class="bg-white/90 backdrop-blur p-4 rounded-2xl border border-slate-200 shadow-lg shadow-slate-100">
                        <label for="hero-pincode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-left">
                            Check Service in Your Area
                        </label>
                        <form @submit.prevent="checkPincode" class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" id="hero-pincode" x-model="pincode" maxlength="6"
                                    placeholder="Enter 6-digit Pincode (e.g. 600032)"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-slate-900 hover:bg-brand-600 disabled:opacity-50 transition-colors whitespace-nowrap">
                                <span x-show="!loading">Check Pincode</span>
                                <span x-show="loading">Checking...</span>
                            </button>
                        </form>

                        <!-- Result Display -->
                        <div x-show="result" x-transition class="mt-3 p-3 rounded-xl text-xs text-left" :class="result?.serviceable ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'">
                            <p class="font-bold" x-text="result?.message"></p>
                            <template x-if="result?.serviceable">
                                <div class="mt-1 text-[11px] text-emerald-700 flex flex-wrap gap-x-4">
                                    <span>Sector: <strong x-text="result?.zone_name"></strong></span>
                                    <span>Delivery: <strong x-text="result?.delivery_charge == 0 ? 'FREE' : '₹' + result?.delivery_charge"></strong></span>
                                    <span>Min Order: <strong x-text="'₹' + result?.min_order_amount"></strong></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Trust Indicators -->
                <div class="pt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left border-t border-slate-200/60">
                    <div>
                        <p class="text-xl font-black text-slate-900">8 Stages</p>
                        <p class="text-xs text-slate-500">RO + UV Purification</p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-slate-900">1:1 Can</p>
                        <p class="text-xs text-slate-500">Empty Exchange</p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-slate-900">4 Daily</p>
                        <p class="text-xs text-slate-500">Delivery Time Slots</p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-slate-900">4.9 ★</p>
                        <p class="text-xs text-slate-500">5,000+ Happy Homes</p>
                    </div>
                </div>
            </div>

            <!-- Right Visual Graphic Column -->
            <div class="lg:col-span-5 flex justify-center">
                <div class="relative w-full max-w-md">
                    <!-- Decorative Backdrop Card -->
                    <div class="relative bg-gradient-to-tr from-brand-600 via-brand-700 to-cyan-600 rounded-3xl p-8 text-white shadow-2xl shadow-brand-600/25 overflow-hidden">
                        <!-- Floating Bubbles -->
                        <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/10 blur-xl"></div>
                        <div class="absolute -left-12 -bottom-12 w-48 h-48 rounded-full bg-cyan-400/20 blur-xl"></div>

                        <!-- Can Mockup Visual -->
                        <div class="text-center py-6 relative z-10">
                            <!-- Water Can SVG Illustration -->
                            <div class="w-48 h-56 mx-auto relative flex items-center justify-center">
                                <svg class="w-full h-full drop-shadow-2xl" viewBox="0 0 200 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Spout Cap -->
                                    <rect x="85" y="10" width="30" height="15" rx="3" fill="#ffffff" opacity="0.95"/>
                                    <!-- Neck -->
                                    <path d="M75 25 H125 L135 50 H65 Z" fill="#bae6fd" opacity="0.9"/>
                                    <!-- Main Can Body -->
                                    <rect x="40" y="50" width="120" height="170" rx="20" fill="url(#can_grad)"/>
                                    <!-- Ribbed Reinforcement Rings -->
                                    <line x1="45" y1="90" x2="155" y2="90" stroke="#ffffff" stroke-width="3" opacity="0.4"/>
                                    <line x1="45" y1="130" x2="155" y2="130" stroke="#ffffff" stroke-width="3" opacity="0.4"/>
                                    <line x1="45" y1="170" x2="155" y2="170" stroke="#ffffff" stroke-width="3" opacity="0.4"/>
                                    <!-- Water Wave Inside -->
                                    <path d="M42 140 Q 70 125, 100 140 T 158 140 V 200 Q 158 218, 140 218 H 60 Q 42 218, 42 200 Z" fill="#38bdf8" opacity="0.7"/>
                                    <!-- Label Badge -->
                                    <rect x="65" y="100" width="70" height="40" rx="8" fill="#ffffff" opacity="0.9"/>
                                    <text x="100" y="118" fill="#0369a1" font-size="12" font-weight="900" text-anchor="middle" font-family="sans-serif">AquaPure</text>
                                    <text x="100" y="132" fill="#0284c7" font-size="10" font-weight="700" text-anchor="middle" font-family="sans-serif">20 LITRES</text>
                                    <defs>
                                        <linearGradient id="can_grad" x1="40" y1="50" x2="160" y2="220" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#e0f2fe" stop-opacity="0.85"/>
                                            <stop offset="0.5" stop-color="#7dd3fc" stop-opacity="0.8"/>
                                            <stop offset="1" stop-color="#0284c7" stop-opacity="0.9"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>

                            <div class="mt-4">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-white text-brand-800 shadow-sm">
                                    Grade-1 Polycarbonate Can
                                </span>
                                <h3 class="text-xl font-black mt-2 text-white">20L Standard Bubble Top</h3>
                                <p class="text-xs text-brand-100">Multi-stage sanitization & tamper-proof seal</p>
                            </div>
                        </div>

                        <!-- Floating Micro Badge 1 -->
                        <div class="absolute top-6 left-4 bg-white/90 backdrop-blur text-slate-800 px-3 py-2 rounded-xl text-xs font-bold shadow-lg flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Doorstep Delivery</span>
                        </div>

                        <!-- Floating Micro Badge 2 -->
                        <div class="absolute bottom-6 right-4 bg-white/90 backdrop-blur text-slate-800 px-3 py-2 rounded-xl text-xs font-bold shadow-lg flex items-center gap-2">
                            <span class="text-brand-600 font-extrabold">₹80</span>
                            <span class="text-[10px] text-slate-500 font-normal">Per Can</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function pincodeChecker() {
        return {
            pincode: '',
            loading: false,
            result: null,
            checkPincode() {
                if (this.pincode.length !== 6) {
                    alert('Please enter a valid 6-digit postal pincode.');
                    return;
                }
                this.loading = true;
                this.result = null;

                fetch('{{ route("pincode.check") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ pincode: this.pincode })
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    this.result = data;
                })
                .catch(err => {
                    this.loading = false;
                    this.result = { serviceable: false, message: 'Unable to check service right now. Please try again.' };
                });
            }
        };
    }
</script>
@endpush
