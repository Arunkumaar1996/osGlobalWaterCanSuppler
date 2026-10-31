<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Branch;
use App\Models\CustomerAddress;
use App\Models\CustomerProfile;
use App\Models\DeliveryPincode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show customer registration view.
     */
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $referrerUserId = null;
        if (! empty($validated['referral_code'])) {
            $referringProfile = CustomerProfile::where('referral_code', $validated['referral_code'])->first();
            if ($referringProfile) {
                $referrerUserId = $referringProfile->user_id;
            }
        }

        // Determine branch based on delivery pincode or fallback to primary branch
        $pincodeRecord = DeliveryPincode::with('zone.branch')
            ->where('pincode', $validated['pincode'])
            ->first();

        $branch = $pincodeRecord?->zone?->branch
            ?? Branch::where('is_primary', true)->first()
            ?? Branch::first();

        $user = DB::transaction(function () use ($validated, $referrerUserId, $branch) {
            // 1. Create User
            $user = User::create([
                'name' => $validated['name'],
                'mobile' => $validated['mobile'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'user_type' => 'customer',
                'is_active' => true,
                'last_login_at' => now(),
            ]);

            // 2. Attach Customer Role
            $customerRole = Role::where('slug', 'customer')->first();
            if ($customerRole) {
                $user->roles()->attach($customerRole->id);
            }

            // 3. Attach Branch Access
            if ($branch) {
                $user->branches()->attach($branch->id, ['is_primary' => true]);
            }

            // 4. Generate Unique Referral Code
            $referralCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $validated['name']), 0, 4)) . rand(1000, 9999);

            // 5. Create Customer Profile
            $customerProfile = CustomerProfile::create([
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'customer_type' => 'residential',
                'outstanding_financial_balance' => 0.00,
                'credit_limit' => 500.00,
                'allow_pay_later' => false,
                'referral_code' => $referralCode,
                'referred_by_user_id' => $referrerUserId,
            ]);

            // 6. Create Default Customer Address
            CustomerAddress::create([
                'customer_profile_id' => $customerProfile->id,
                'address_type' => 'home',
                'contact_person_name' => $validated['name'],
                'contact_person_mobile' => $validated['mobile'],
                'house_flat_no' => $validated['house_flat_no'],
                'building_apartment_name' => $validated['building_apartment_name'] ?? null,
                'street_address' => $validated['street_address'],
                'landmark' => $validated['landmark'] ?? null,
                'area' => $validated['area'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'pincode' => $validated['pincode'],
                'delivery_instructions' => $validated['delivery_instructions'] ?? null,
                'is_default' => true,
                'is_active' => true,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')->with('success', 'Welcome to AquaFlow! Your account has been registered successfully.');
    }
}
