<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'active', 'branch.context', 'role:customer'])
                ->prefix('customer')
                ->name('customer.')
                ->group(base_path('routes/customer.php'));

            Route::middleware(['web', 'auth', 'active', 'branch.context', 'role:super-admin,business-admin,branch-manager,order-manager,inventory-manager,accounts-manager,delivery-supervisor'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'auth', 'active', 'branch.context', 'role:delivery-staff,delivery-supervisor'])
                ->prefix('staff')
                ->name('staff.')
                ->group(base_path('routes/staff.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/razorpay',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureActiveUser::class,
            \App\Http\Middleware\SetBranchContext::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
            'active' => \App\Http\Middleware\EnsureActiveUser::class,
            'branch.context' => \App\Http\Middleware\SetBranchContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
