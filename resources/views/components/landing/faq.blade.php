@props(['faqs'])

<section id="faq" class="py-16 sm:py-24 bg-white border-b border-slate-200/60" x-data="{ activeFaq: 1 }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-100">
                Got Questions?
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                Frequently Asked Questions
            </h2>
            <p class="text-sm sm:text-base text-slate-500 mt-2">
                Everything you need to know about our water delivery, container exchange model, and billing.
            </p>
        </div>

        <!-- Accordion Stack -->
        <div class="space-y-4">
            @forelse($faqs as $faq)
                <div class="rounded-2xl border border-slate-200 overflow-hidden transition-colors" :class="activeFaq === {{ $faq->id }} ? 'bg-slate-50/80 border-brand-300' : 'bg-white'">
                    <button type="button" @click="activeFaq = (activeFaq === {{ $faq->id }} ? null : {{ $faq->id }})" class="w-full px-6 py-4 text-left flex items-center justify-between gap-4 font-bold text-sm text-slate-900 hover:text-brand-600">
                        <span>{{ $faq->question }}</span>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="activeFaq === {{ $faq->id }} ? 'rotate-180 text-brand-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="activeFaq === {{ $faq->id }}" x-transition class="px-6 pb-5 pt-1 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
                        {{ $faq->answer }}
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-xs text-slate-500">
                    No FAQs available right now.
                </div>
            @endforelse
        </div>
    </div>
</section>
