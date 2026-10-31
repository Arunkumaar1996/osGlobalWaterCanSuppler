<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CanManagementController;
use App\Http\Controllers\Admin\DeliveryDispatchController;
use App\Http\Controllers\Admin\FulfillmentController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProcurementController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Handled by authorized administrative personnel across branches.
| Prefix: /admin
| Route Name Prefix: admin.
|
*/

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');

// Products Catalog Management
Route::resource('products', ProductController::class);
Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
Route::get('products/{product}/price-history', [ProductController::class, 'priceHistory'])->name('products.price-history');

// Supplier Management & Ledger
Route::resource('suppliers', SupplierController::class);
Route::post('suppliers/{supplier}/products', [SupplierController::class, 'mapProduct'])->name('suppliers.products.store');
Route::delete('suppliers/{supplier}/products/{supplierProduct}', [SupplierController::class, 'unmapProduct'])->name('suppliers.products.destroy');
Route::post('suppliers/{supplier}/payments', [SupplierController::class, 'recordPayment'])->name('suppliers.payments.store');

// Procurement & Goods Receipts (GRN)
Route::get('procurement', [ProcurementController::class, 'index'])->name('procurement.index');
Route::get('procurement/create', [ProcurementController::class, 'create'])->name('procurement.create');
Route::post('procurement', [ProcurementController::class, 'store'])->name('procurement.store');
Route::get('procurement/{purchaseOrder}', [ProcurementController::class, 'show'])->name('procurement.show');
Route::get('procurement/{purchaseOrder}/grn', [ProcurementController::class, 'createGrn'])->name('procurement.grn.create');
Route::post('procurement/{purchaseOrder}/grn', [ProcurementController::class, 'storeGrn'])->name('procurement.grn.store');

// Multi-Category Warehouse Inventory & Transfers
Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('inventory/{warehouseStock}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
Route::post('inventory/{warehouseStock}/cycle-cleaning', [InventoryController::class, 'cycleCleaning'])->name('inventory.cycle-cleaning');
Route::post('inventory/{warehouseStock}/record-damaged', [InventoryController::class, 'recordDamaged'])->name('inventory.record-damaged');
Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
Route::get('inventory/transfers', [InventoryController::class, 'transfersIndex'])->name('inventory.transfers.index');
Route::get('inventory/transfers/create', [InventoryController::class, 'transfersCreate'])->name('inventory.transfers.create');
Route::post('inventory/transfers', [InventoryController::class, 'transfersStore'])->name('inventory.transfers.store');
Route::post('inventory/transfers/{stockTransfer}/complete', [InventoryController::class, 'transfersComplete'])->name('inventory.transfers.complete');

// Customer Orders & Split-Fulfillment Engine
Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

// Split Sourcing Allocation & Dispatches
Route::post('orders/{order}/fulfillments', [FulfillmentController::class, 'store'])->name('orders.fulfillments.store');
Route::post('fulfillments/{fulfillment}/dispatch', [FulfillmentController::class, 'dispatch'])->name('fulfillments.dispatch');
Route::post('fulfillments/{fulfillment}/cancel', [FulfillmentController::class, 'cancel'])->name('fulfillments.cancel');

// Dispatch Manifests & Packing Slips
Route::get('manifests', [FulfillmentController::class, 'manifestsIndex'])->name('manifests.index');
Route::get('manifests/create', [FulfillmentController::class, 'manifestsCreate'])->name('manifests.create');
Route::post('manifests', [FulfillmentController::class, 'manifestsStore'])->name('manifests.store');
Route::get('manifests/{dispatchManifest}', [FulfillmentController::class, 'manifestsShow'])->name('manifests.show');

// Customer Accounts, Double-Entry Ledgers & Collections
Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
Route::get('accounts/{customer}/ledger', [AccountController::class, 'customerLedger'])->name('accounts.customer');
Route::post('accounts/{customer}/collect', [AccountController::class, 'recordCollection'])->name('accounts.collect');
Route::post('accounts/{customer}/adjust', [AccountController::class, 'adjustBalance'])->name('accounts.adjust');
Route::get('payments', [AccountController::class, 'paymentsList'])->name('payments.index');

// Empty-Can Exchange Management & Circulation Fleet
Route::get('cans', [CanManagementController::class, 'index'])->name('cans.index');
Route::get('cans/{customer}', [CanManagementController::class, 'customerBalance'])->name('cans.customer');
Route::post('cans/{customer}/return', [CanManagementController::class, 'recordReturn'])->name('cans.return');
Route::post('cans/{customer}/penalty', [CanManagementController::class, 'chargePenalty'])->name('cans.penalty');
Route::post('cans/{customer}/reconcile', [CanManagementController::class, 'reconcile'])->name('cans.reconcile');
Route::get('cans-movements', [CanManagementController::class, 'movementsAudit'])->name('cans.movements');

// Delivery Fleet, Runsheets, Partners & Capacity Engine
Route::get('delivery', [DeliveryDispatchController::class, 'index'])->name('delivery.index');
Route::get('delivery/trips/create', [DeliveryDispatchController::class, 'createTrip'])->name('delivery.trips.create');
Route::post('delivery/trips', [DeliveryDispatchController::class, 'storeTrip'])->name('delivery.trips.store');
Route::get('delivery/trips/{trip}', [DeliveryDispatchController::class, 'showTrip'])->name('delivery.trips.show');
Route::post('delivery/trips/{trip}/dispatch', [DeliveryDispatchController::class, 'dispatchTrip'])->name('delivery.trips.dispatch');
Route::post('delivery/trips/{trip}/complete', [DeliveryDispatchController::class, 'completeTrip'])->name('delivery.trips.complete');
Route::post('delivery/stops/{stop}/complete', [DeliveryDispatchController::class, 'completeStop'])->name('delivery.stops.complete');
Route::post('delivery/stops/{stop}/fail', [DeliveryDispatchController::class, 'failStop'])->name('delivery.stops.fail');

// 3rd-Party Delivery Partners
Route::get('delivery-partners', [DeliveryDispatchController::class, 'partnersIndex'])->name('delivery.partners.index');
Route::post('delivery-partners', [DeliveryDispatchController::class, 'partnerStore'])->name('delivery.partners.store');

// Delivery Slots & Capacity Limits
Route::get('delivery-slots', [DeliveryDispatchController::class, 'slotsIndex'])->name('delivery.slots.index');
Route::post('delivery-slots/{slot}/toggle-block', [DeliveryDispatchController::class, 'slotToggleBlock'])->name('delivery.slots.toggle-block');

// Customer Recurring Subscriptions
Route::get('subscriptions', [\App\Http\Controllers\Admin\SubscriptionManagementController::class, 'index'])->name('subscriptions.index');
Route::get('subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionManagementController::class, 'show'])->name('subscriptions.show');
Route::post('subscriptions/generate-manual', [\App\Http\Controllers\Admin\SubscriptionManagementController::class, 'generateManual'])->name('subscriptions.generate-manual');

// Operating Expenses
Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class)->only(['index', 'create', 'store', 'destroy']);

// Business Analytics, P&L & Reporting
Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
Route::get('reports/pnl', [\App\Http\Controllers\Admin\ReportController::class, 'pnl'])->name('reports.pnl');
Route::get('reports/containers', [\App\Http\Controllers\Admin\ReportController::class, 'containers'])->name('reports.containers');
Route::get('reports/fleet', [\App\Http\Controllers\Admin\ReportController::class, 'fleet'])->name('reports.fleet');
Route::get('reports/export/orders', [\App\Http\Controllers\Admin\ReportController::class, 'exportOrders'])->name('reports.export-orders');
Route::get('reports/export/expenses', [\App\Http\Controllers\Admin\ReportController::class, 'exportExpenses'])->name('reports.export-expenses');
Route::get('reports/export/dues', [\App\Http\Controllers\Admin\ReportController::class, 'exportDues'])->name('reports.export-dues');

// System Utilities & Shared Hosting Helpers (Zero-SSH Console)
Route::post('utilities/storage-link', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return back()->with('success', 'Public storage link verified and created successfully.');
    } catch (\Throwable $e) {
        return back()->withErrors(['error' => 'Storage link error: ' . $e->getMessage()]);
    }
})->name('utilities.storage-link');

Route::post('utilities/optimize-clear', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return back()->with('success', 'Application cache, routes, and views cleared successfully.');
    } catch (\Throwable $e) {
        return back()->withErrors(['error' => 'Cache clear error: ' . $e->getMessage()]);
    }
})->name('utilities.optimize-clear');

// User Management & Roles/Permissions Matrix
Route::get('users/permissions', [UserManagementController::class, 'permissions'])->name('users.permissions');
Route::post('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
Route::resource('users', UserManagementController::class);

// Branch & Business Settings
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('settings/branch/{branch}', [SettingController::class, 'updateBranch'])->name('settings.branch.update');
Route::post('settings/storage-link', [SettingController::class, 'storageLink'])->name('settings.storage-link');
Route::post('settings/optimize-clear', [SettingController::class, 'optimizeClear'])->name('settings.optimize-clear');




