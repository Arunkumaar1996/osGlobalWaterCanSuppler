<?php

use App\Http\Controllers\Staff\DeliveryStaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Delivery Staff Mobile Routes
|--------------------------------------------------------------------------
|
| Handled by delivery executives on mobile devices for dispatch runs,
| OTP verification, empty can collections, and cash receipts.
|
*/

Route::get('/deliveries', [DeliveryStaffController::class, 'deliveries'])->name('deliveries');
Route::post('/stops/{stop}/verify', [DeliveryStaffController::class, 'verifyStop'])->name('stops.verify');
Route::post('/stops/{stop}/fail', [DeliveryStaffController::class, 'failStop'])->name('stops.fail');
Route::get('/cash-log', [DeliveryStaffController::class, 'cashLog'])->name('cash-log');
