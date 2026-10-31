@props(['testimonials'])

<section class="py-16 sm:py-24 bg-slate-50/70 border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-100">
                Customer Stories
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                Loved by Residents & Corporates Alike
            </h2>
            <p class="text-sm sm:text-base text-slate-500 mt-2">
                Discover what our verified customers say about our hygienic water and prompt doorstep service.
            </p>
        </div>

        <!-- Testimonials Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($testimonials as $t)
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm hover:border-brand-300 hover-lift transition-all flex flex-col justify-between">
                    <div>
                        <!-- 5 Star Rating -->
                        <div class="flex items-center gap-1 text-amber-400 mb-4">
                            @for($i = 0; $i < $t->rating; $i++)
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>

                        <!-- Quote -->
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed italic">
                            &ldquo;{{ $t->comment }}&rdquo;
                        </p>
                    </div>

                    <!-- Author Info -->
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs">
                            {{ strtoupper(substr($t->customer_name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">{{ $t->customer_name }}</h4>
                            <p class="text-[11px] text-slate-500">{{ $t->customer_designation_or_area }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-8 text-xs text-slate-500">
                    No customer testimonials added yet.
                </div>
            @endforelse
        </div>
    </div>
</section>
