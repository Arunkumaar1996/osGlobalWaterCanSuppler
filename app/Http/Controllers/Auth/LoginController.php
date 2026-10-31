<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Dynamic redirect based on user role
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->isAdmin() || $user->hasRole(['super-admin', 'business-admin', 'branch-manager', 'order-manager', 'inventory-manager', 'accounts-manager', 'delivery-supervisor'])) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isStaff() || $user->hasRole('delivery-staff')) {
            return redirect()->intended(route('staff.deliveries'));
        }

        return redirect()->intended(route('customer.dashboard'));
    }
}
