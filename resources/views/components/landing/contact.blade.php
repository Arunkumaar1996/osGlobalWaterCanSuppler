<section id="contact" class="py-16 sm:py-24 bg-slate-50/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-100">
                Get In Touch
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                Contact Our Hub Team
            </h2>
            <p class="text-sm sm:text-base text-slate-500 mt-2">
                Have a commercial requirement, corporate subscription query, or feedback? Send us a message.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Left Info Panel -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-6">
                    <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">
                        Central Distribution Office
                    </h3>

                    <!-- Address -->
                    <div class="flex items-start gap-4 text-xs">
                        <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </div>
                        <div>
                            <strong class="text-slate-900 block text-xs">Distribution Hub</strong>
                            <span class="text-slate-500 leading-relaxed block mt-0.5">Plot 42, Industrial Estate Phase 2, Guindy, Chennai, Tamil Nadu - 600032</span>
                        </div>
                    </div>

                    <!-- Phone & WhatsApp -->
                    <div class="flex items-start gap-4 text-xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <strong class="text-slate-900 block text-xs">Phone & WhatsApp</strong>
                            <span class="text-slate-600 font-medium block mt-0.5">+91 98765 00001 / +91 98765 00002</span>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start gap-4 text-xs">
                        <div class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <strong class="text-slate-900 block text-xs">Support Email</strong>
                            <span class="text-slate-600 font-medium block mt-0.5">contact@aquaflow.com</span>
                        </div>
                    </div>

                    <!-- Business Hours -->
                    <div class="flex items-start gap-4 text-xs">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <strong class="text-slate-900 block text-xs">Delivery & Dispatch Hours</strong>
                            <span class="text-slate-500 block mt-0.5">Monday – Sunday: 7:00 AM – 8:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Interactive Contact Form -->
            <div class="lg:col-span-7">
                <div class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/80 shadow-sm" x-data="contactFormHandler()">
                    <h3 class="text-base font-bold text-slate-900 mb-6 pb-2 border-b border-slate-100">
                        Send An Inquiry or Request Corporate Samples
                    </h3>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="inquiry_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Your Name *</label>
                                <input type="text" id="inquiry_name" x-model="form.name" required
                                    placeholder="e.g. Anand R."
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>

                            <div>
                                <label for="inquiry_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Number *</label>
                                <input type="tel" id="inquiry_phone" x-model="form.phone" required maxlength="10"
                                    placeholder="9876543210"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="inquiry_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                                <input type="email" id="inquiry_email" x-model="form.email"
                                    placeholder="anand@company.com"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>

                            <div>
                                <label for="inquiry_subject" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Inquiry Subject</label>
                                <select id="inquiry_subject" x-model="form.subject" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                    <option value="Residential Delivery">Residential Home Water Can Delivery</option>
                                    <option value="Corporate Bulk Delivery">Corporate Bulk Office Supply</option>
                                    <option value="Apartment Society Supply">Apartment Society Water Partnership</option>
                                    <option value="New Area Request">Request Service in New Pincode</option>
                                    <option value="Other">General Question</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="inquiry_message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Message / Requirements *</label>
                            <textarea id="inquiry_message" x-model="form.message" rows="4" required
                                placeholder="Please detail your approximate can count, preferred delivery time, and delivery address..."
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                        </div>

                        <button type="submit" :disabled="submitting" class="w-full py-3.5 px-6 rounded-xl text-xs font-extrabold text-white bg-slate-900 hover:bg-brand-600 disabled:opacity-50 shadow-md hover-lift transition-all">
                            <span x-show="!submitting">Submit Inquiry</span>
                            <span x-show="submitting">Submitting your inquiry...</span>
                        </button>
                    </form>

                    <div x-show="message" x-transition class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                        <span x-text="message"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function contactFormHandler() {
        return {
            form: {
                name: '',
                phone: '',
                email: '',
                subject: 'Residential Delivery',
                message: ''
            },
            submitting: false,
            message: null,
            submitForm() {
                if (this.form.phone.length !== 10) {
                    alert('Please enter a valid 10-digit mobile number.');
                    return;
                }
                this.submitting = true;
                this.message = null;

                fetch('{{ route("contact.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.form)
                })
                .then(res => res.json())
                .then(data => {
                    this.submitting = false;
                    this.message = data.message;
                    this.form = { name: '', phone: '', email: '', subject: 'Residential Delivery', message: '' };
                })
                .catch(err => {
                    this.submitting = false;
                    alert('Error submitting inquiry. Please call our hub directly at +91 98765 00001.');
                });
            }
        };
    }
</script>
@endpush
