@props(['products'])

<section id="products" class="py-16 sm:py-24 bg-white border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-100">
                Pure Hydration Choices
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                Our Water Can Capacities & Products
            </h2>
            <p class="text-sm sm:text-base text-slate-500 mt-2">
                Dynamically managed sizes for every family, home kitchen, and corporate work floor.
            </p>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="bg-slate-50/70 hover:bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-brand-300 hover-lift transition-all flex flex-col justify-between">
                    <div>
                        <!-- Top Tag & Capacity Pill -->
                        <div class="flex items-center justify-between mb-4">
                            @if($product->is_featured)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-brand-600 text-white shadow-sm">
                                    Popular
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold text-slate-600 bg-slate-200/60">
                                    {{ $product->category?->name ?? 'Packaged Water' }}
                                </span>
                            @endif

                            <span class="text-xs font-extrabold text-brand-700 bg-brand-100/70 px-2.5 py-1 rounded-lg">
                                {{ $product->capacity_value }} {{ $product->capacity_unit }}
                            </span>
                        </div>

                        <!-- Product Visual Art -->
                        <div class="h-40 my-2 rounded-2xl bg-gradient-to-b from-brand-50/50 to-cyan-50/40 flex items-center justify-center p-4">
                            <svg class="h-28 drop-shadow-md" viewBox="0 0 100 130" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="42" y="5" width="16" height="8" rx="2" fill="#0284c7"/>
                                <path d="M36 13 H64 L70 30 H30 Z" fill="#7dd3fc"/>
                                <rect x="20" y="30" width="60" height="90" rx="12" fill="#38bdf8" opacity="0.85"/>
                                <line x1="22" y1="55" x2="78" y2="55" stroke="#ffffff" stroke-width="2" opacity="0.5"/>
                                <line x1="22" y1="80" x2="78" y2="80" stroke="#ffffff" stroke-width="2" opacity="0.5"/>
                                <line x1="22" y1="105" x2="78" y2="105" stroke="#ffffff" stroke-width="2" opacity="0.5"/>
                                <rect x="32" y="62" width="36" height="24" rx="4" fill="#ffffff" opacity="0.95"/>
                                <text x="50" y="78" fill="#0369a1" font-size="8" font-weight="900" text-anchor="middle" font-family="sans-serif">{{ (int)$product->capacity_value }}L</text>
                            </svg>
                        </div>

                        <!-- Product Information -->
                        <h3 class="text-base font-bold text-slate-900 mt-2">{{ $product->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                            {{ $product->short_description ?? 'Multi-stage RO & UV purified packaged drinking water with tamper-evident seal.' }}
                        </p>
                    </div>

                    <!-- Bottom Pricing & Action -->
                    <div class="mt-6 pt-4 border-t border-slate-200/60">
                        <div class="flex items-baseline justify-between mb-3">
                            <div>
                                <span class="text-2xl font-black text-slate-900">₹{{ number_format($product->selling_price, 2) }}</span>
                                <span class="text-[10px] text-slate-400 block font-medium">Incl. all taxes</span>
                            </div>

                            @if($product->requires_can_exchange)
                                <span class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200/60">
                                    1:1 Exchange
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">
                                    No Exchange
                                </span>
                            @endif
                        </div>

                        <!-- Order / Add to Cart Form -->
                        <form method="POST" action="{{ route('cart.add') }}" x-data="{ qty: 1 }" class="space-y-2.5">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-semibold text-slate-500">Quantity:</span>
                                <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                                    <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 text-xs font-bold transition-colors">&minus;</button>
                                    <input type="number" name="quantity" x-model.number="qty" min="1" max="50" class="w-10 text-center text-xs font-bold bg-white border-0 py-1 focus:ring-0">
                                    <button type="button" @click="qty = Math.min(50, qty + 1)" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 text-xs font-bold transition-colors">&plus;</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <button type="submit" name="buy_now" value="0" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Add Cart</span>
                                </button>

                                <button type="submit" name="buy_now" value="1" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-500/20 transition-colors">
                                    <span>Buy Now</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-4 py-12 text-center">
                    <p class="text-sm text-slate-500">No products configured yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
