@props(['deliveryZones'])

<section id="service-areas" class="py-16 sm:py-24 bg-slate-50/70 border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-100">
                Coverage Sectors
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                Serviceable Delivery Areas
            </h2>
            <p class="text-sm sm:text-base text-slate-500 mt-2">
                We operate prompt morning and evening delivery routes across major Chennai residential & commercial sectors.
            </p>
        </div>

        <!-- Zones Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            @foreach($deliveryZones as $zone)
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                        <div>
                            <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Sector Hub</span>
                            <h3 class="text-lg font-bold text-slate-900 mt-0.5">{{ $zone->name }}</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active Fleet
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 mb-4">{{ $zone->description }}</p>

                    <!-- Pincodes Chips -->
                    <div class="flex flex-wrap gap-2">
                        @foreach($zone->pincodes as $pin)
                            <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs flex items-center gap-1.5 hover:border-brand-300 transition-colors">
                                <span class="font-bold text-slate-800">{{ $pin->pincode }}</span>
                                <span class="text-slate-400">&bull;</span>
                                <span class="text-slate-600 text-[11px]">{{ $pin->area_name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Not in Service Area Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl">
            <div class="text-center sm:text-left">
                <h4 class="text-lg font-bold">Don't see your pincode listed?</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-lg">
                    We are expanding to additional Chennai localities every month. Send us an inquiry and our distribution manager will notify you when deliveries begin in your street.
                </p>
            </div>
            <a href="#contact" class="px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-500 font-bold text-xs text-white shadow-md hover-lift transition-all whitespace-nowrap">
                Request Coverage in My Area
            </a>
        </div>
    </div>
</section>
