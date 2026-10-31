<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public landing page, cart, checkout, and authentication endpoints.
|
*/

// Public Landing Page & Interactive Endpoints
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::post('/check-pincode', [LandingPageController::class, 'checkPincode'])->name('pincode.check');
Route::post('/contact-inquiry', [LandingPageController::class, 'submitContactInquiry'])->name('contact.submit');

// PWA Offline Fallback View & Service Worker Endpoints
Route::get('/offline', function () {
    return view('errors.offline');
})->name('offline');

Route::get('/manifest.json', function () {
    return response(file_get_contents(public_path('manifest.json')), 200, [
        'Content-Type' => 'application/manifest+json',
    ]);
});

Route::get('/sw.js', function () {
    return response(file_get_contents(public_path('sw.js')), 200, [
        'Content-Type' => 'application/javascript',
    ]);
});

// Shopping Cart Routes (Open to Guests & Customers)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout Routes (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/get-slots', [CheckoutController::class, 'getSlots'])->name('checkout.get-slots');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/address', [CheckoutController::class, 'storeAddress'])->name('checkout.address.store');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

// Authenticated Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Razorpay Server-to-Server Webhook
Route::post('/webhooks/razorpay', [\App\Http\Controllers\Customer\PaymentController::class, 'webhook'])->name('razorpay.webhook');
