@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-md mx-auto w-full">
    <div class="text-center mb-8">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Sign In to Your Account</h2>
        <p class="mt-2 text-sm text-slate-600">
            Or <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-500">create a new water delivery account</a>
        </p>
    </div>

    <!-- Login Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100">
        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <!-- Mobile or Email -->
            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mobile Number or Email</label>
                <div class="relative">
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                        placeholder="e.g. 9876543210 or admin@aquaflow.com"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors @error('login') border-rose-500 bg-rose-50/30 @enderror">
                </div>
                @error('login')
                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div x-data="{ show: false }">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                    <a href="#" class="text-xs font-semibold text-brand-600 hover:text-brand-500">Forgot?</a>
                </div>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" required
                        placeholder="Enter your password"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors @error('password') border-rose-500 bg-rose-50/30 @enderror">
                    <button type="button" @click="show = !show" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 text-xs font-semibold">
                        <span x-text="show ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <label for="remember" class="ml-2 block text-xs font-semibold text-slate-700">Remember this device</label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-500/20 hover-lift transition-all">
                Sign In
            </button>
        </form>

        <!-- Demo Credentials Helper Pill Box -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider text-center mb-3">Quick Demo Logins (Click to fill)</p>
            <div class="space-y-2">
                <button type="button" onclick="fillLogin('9876543210', 'password123')" class="w-full text-left px-3 py-2 bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-200 text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">Super Admin</span>
                        <span class="text-slate-500 block">admin@aquaflow.com (9876543210)</span>
                    </div>
                    <span class="text-brand-600 font-semibold text-[11px]">Use &rarr;</span>
                </button>
                <button type="button" onclick="fillLogin('9876543212', 'password123')" class="w-full text-left px-3 py-2 bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-200 text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">Delivery Staff</span>
                        <span class="text-slate-500 block">delivery@aquaflow.com (9876543212)</span>
                    </div>
                    <span class="text-brand-600 font-semibold text-[11px]">Use &rarr;</span>
                </button>
                <button type="button" onclick="fillLogin('9876543213', 'password123')" class="w-full text-left px-3 py-2 bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-200 text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">Customer</span>
                        <span class="text-slate-500 block">customer@gmail.com (9876543213)</span>
                    </div>
                    <span class="text-brand-600 font-semibold text-[11px]">Use &rarr;</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function fillLogin(login, password) {
        document.getElementById('login').value = login;
        document.getElementById('password').value = password;
    }
</script>
@endpush
@endsection
