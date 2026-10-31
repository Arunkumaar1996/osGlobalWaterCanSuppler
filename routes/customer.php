<?php

use App\Http\Controllers\Customer\CanBalanceController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\SubscriptionController;
use App\Http\Controllers\CustomerOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
|
| Handled by authenticated customers for ordering, addresses,
| order tracking, dues settlement, can balances, and subscriptions.
|
*/

Route::get('/dashboard', [CustomerOrderController::class, 'dashboard'])->name('dashboard');

// Order History & Tracking
Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/checkout', [PaymentController::class, 'createOrderCheckout'])->name('orders.checkout');
Route::post('/orders/{order}/verify', [PaymentController::class, 'verifyOrderPayment'])->name('orders.verify');

// Dues & Instant Online Settlement
Route::get('/dues', [PaymentController::class, 'dues'])->name('dues.index');
Route::post('/dues/checkout', [PaymentController::class, 'createSettlementCheckout'])->name('dues.checkout');
Route::post('/dues/verify', [PaymentController::class, 'verifySettlementPayment'])->name('dues.verify');

// Financial Passbook / Double-Entry Ledger
Route::get('/ledger', [PaymentController::class, 'ledger'])->name('ledger.index');

// Empty-Can Container Balance & Movement Passbook
Route::get('/cans', [CanBalanceController::class, 'index'])->name('cans.index');

// Recurring Subscriptions
Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');
Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
Route::post('/subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
Route::post('/subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
