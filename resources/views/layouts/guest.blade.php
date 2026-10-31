@extends('layouts.base')

@section('body')
<div class="min-h-full flex flex-col justify-between" x-data="{ mobileMenuOpen: false }">
    <!-- Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-cyan-400 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-slate-900 block leading-tight">AquaFlow</span>
                        <span class="text-xs text-brand-600 font-semibold tracking-wider uppercase block">Water Logistics</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Home</a>
                    <a href="#products" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Products</a>
                    <a href="#how-it-works" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">How It Works</a>
                    <a href="#service-areas" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Service Areas</a>
                    <a href="#faq" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">FAQ</a>
                    <a href="#contact" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Contact</a>
                </nav>

                <!-- Cart Button -->
                <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-1.5 p-2 rounded-xl text-slate-700 hover:text-brand-600 hover:bg-slate-100 transition-colors" title="View Cart">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="text-xs font-bold hidden sm:inline">Cart</span>
                </a>

                <!-- Auth Buttons / User Profile -->
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors">
                                Admin Dashboard &rarr;
                            </a>
                        @elseif(auth()->user()->isStaff())
                            <a href="{{ route('staff.deliveries') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors">
                                Staff Portal &rarr;
                            </a>
                        @else
                            <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors">
                                My Account &rarr;
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-brand-600 px-3 py-2 transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-md shadow-brand-500/20 hover-lift transition-all">
                            Order Water Now
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center md:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-b border-slate-200 bg-white px-4 pt-2 pb-6 space-y-3">
            <a href="{{ route('home') }}" class="block px-3 py-2 text-base font-semibold text-slate-800 rounded-lg hover:bg-slate-50">Home</a>
            <a href="#products" class="block px-3 py-2 text-base font-semibold text-slate-800 rounded-lg hover:bg-slate-50">Our Products</a>
            <a href="#how-it-works" class="block px-3 py-2 text-base font-semibold text-slate-800 rounded-lg hover:bg-slate-50">How It Works</a>
            <a href="#service-areas" class="block px-3 py-2 text-base font-semibold text-slate-800 rounded-lg hover:bg-slate-50">Service Areas</a>
            <a href="#faq" class="block px-3 py-2 text-base font-semibold text-slate-800 rounded-lg hover:bg-slate-50">FAQ</a>
            <a href="#contact" class="block px-3 py-2 text-base font-semibold text-slate-800 rounded-lg hover:bg-slate-50">Contact Us</a>
            <a href="{{ route('cart.index') }}" class="block px-3 py-2 text-base font-semibold text-brand-600 rounded-lg hover:bg-brand-50 font-bold">🛒 View Shopping Cart</a>
            
            <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="w-full text-center py-2.5 px-4 text-sm font-semibold text-white bg-brand-600 rounded-xl">Go to Admin Dashboard</a>
                    @elseif(auth()->user()->isStaff())
                        <a href="{{ route('staff.deliveries') }}" class="w-full text-center py-2.5 px-4 text-sm font-semibold text-white bg-brand-600 rounded-xl">Go to Delivery Staff Portal</a>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="w-full text-center py-2.5 px-4 text-sm font-semibold text-white bg-brand-600 rounded-xl">Customer Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 px-4 text-sm font-semibold text-slate-800 bg-slate-100 rounded-xl">Sign In</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 px-4 text-sm font-semibold text-white bg-brand-600 rounded-xl">Register & Order Now</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Body Content -->
    <main class="flex-grow">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 mt-4">
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="max-w-7xl mx-auto px-4 mt-4">
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 text-sm border-t border-slate-800 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center text-white">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                        </div>
                        <span class="text-lg font-bold text-white">AquaFlow</span>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-400">
                        Reliable, pure packaged drinking water delivery platform for residential homes and corporate enterprises. Flexible slots, empty can exchange, and transparent billing.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="#products" class="hover:text-white transition-colors">Our Water Cans</a></li>
                        <li><a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a></li>
                        <li><a href="#service-areas" class="hover:text-white transition-colors">Serviceable Pincodes</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Customer Support</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Create Account</a></li>
                        <li><a href="#faq" class="hover:text-white transition-colors">Frequently Asked Questions</a></li>
                        <li><a href="#contact" class="hover:text-white transition-colors">Raise a Support Ticket</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Contact & Hub</h4>
                    <p class="text-xs text-slate-400 mb-2">Central Distribution Hub, Guindy Industrial Estate, Chennai</p>
                    <p class="text-xs text-slate-400 mb-1">Phone: <span class="text-white font-medium">+91 98765 00001</span></p>
                    <p class="text-xs text-slate-400">Email: <span class="text-white font-medium">contact@aquaflow.com</span></p>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} AquaFlow Solutions. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-slate-400">Terms of Service</a>
                    <a href="#" class="hover:text-slate-400">Privacy Policy</a>
                    <a href="#" class="hover:text-slate-400">Can Exchange Terms</a>
                </div>
            </div>
        </div>
    </footer>
</div>
@endsection
