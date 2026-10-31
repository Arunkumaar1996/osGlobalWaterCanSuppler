<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Settings Dashboard: Branch Details, Logistics, Payments, and System Utilities.
     */
    public function index(): View
    {
        $business = Business::first() ?? new Business(['name' => 'AquaFlow Pure Water']);
        $branch = Branch::where('is_primary', true)->first() ?? Branch::first() ?? new Branch([
            'name' => 'Central Chennai Distribution Hub',
            'code' => 'CHN-01',
            'phone' => '+91 98765 43210',
            'email' => 'operations@aquaflow.com',
            'address' => 'Plot 45, Industrial Estate, Guindy',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600032',
        ]);

        $branches = Branch::all();

        // System Diagnostic Flags
        $storageLinked = is_link(public_path('storage')) || file_exists(public_path('storage'));
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        return view('admin.settings.index', compact(
            'business',
            'branch',
            'branches',
            'storageLinked',
            'phpVersion',
            'laravelVersion'
        ));
    }

    /**
     * Update Branch Information.
     */
    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20'],
        ]);

        $branch->update($validated);

        return back()->with('success', "Branch details for '{$branch->name}' updated successfully.");
    }

    /**
     * Trigger Public Storage Link.
     */
    public function storageLink(): RedirectResponse
    {
        try {
            Artisan::call('storage:link');
            return back()->with('success', 'Public storage link verified and created successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Storage link error: ' . $e->getMessage()]);
        }
    }

    /**
     * Trigger Application Cache & Route Invalidation.
     */
    public function optimizeClear(): RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');
            return back()->with('success', 'Application cache, compiled views, and routes cleared successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Cache clear error: ' . $e->getMessage()]);
        }
    }
}
