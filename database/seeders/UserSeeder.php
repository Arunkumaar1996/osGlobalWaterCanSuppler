<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CustomerAddress;
use App\Models\CustomerProfile;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();

        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['mobile' => '9876543210'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@aquaflow.com',
                'password' => Hash::make('password123'),
                'user_type' => 'admin',
                'is_active' => true,
            ]
        );
        $adminRole = Role::where('slug', 'super-admin')->first();
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }
        if ($branch) {
            $admin->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);
        }

        // 2. Branch Manager
        $manager = User::firstOrCreate(
            ['mobile' => '9876543211'],
            [
                'name' => 'Rajesh Sharma (Hub Manager)',
                'email' => 'manager@aquaflow.com',
                'password' => Hash::make('password123'),
                'user_type' => 'admin',
                'is_active' => true,
            ]
        );
        $managerRole = Role::where('slug', 'branch-manager')->first();
        if ($managerRole) {
            $manager->roles()->syncWithoutDetaching([$managerRole->id]);
        }
        if ($branch) {
            $manager->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);
        }

        // 3. Delivery Staff
        $staff = User::firstOrCreate(
            ['mobile' => '9876543212'],
            [
                'name' => 'Senthil Kumar (Delivery Lead)',
                'email' => 'delivery@aquaflow.com',
                'password' => Hash::make('password123'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $staffRole = Role::where('slug', 'delivery-staff')->first();
        if ($staffRole) {
            $staff->roles()->syncWithoutDetaching([$staffRole->id]);
        }
        if ($branch) {
            $staff->branches()->syncWithoutDetaching([$branch->id => ['is_primary' => true]]);
            StaffProfile::firstOrCreate(
                ['user_id' => $staff->id],
                [
                    'branch_id' => $branch->id,
                    'designation' => 'Senior Delivery Executive',
                    'vehicle_number' => 'TN-09-CB-4521',
                    'driving_license_no' => 'DL-TN09201900341',
                    'emergency_contact' => '+91 94440 98765',
                    'current_cash_in_hand' => 0.00,
                    'is_available_for_delivery' => true,
                ]
            );
        }

        // 4. Test Residential Customer
        $customerUser = User::firstOrCreate(
            ['mobile' => '9876543213'],
            [
                'name' => 'Anand Krishnamurthy',
                'email' => 'customer@gmail.com',
                'password' => Hash::make('password123'),
                'user_type' => 'customer',
                'is_active' => true,
            ]
        );
        $custRole = Role::where('slug', 'customer')->first();
        if ($custRole) {
            $customerUser->roles()->syncWithoutDetaching([$custRole->id]);
        }
        if ($branch) {
            $customerProfile = CustomerProfile::firstOrCreate(
                ['user_id' => $customerUser->id],
                [
                    'branch_id' => $branch->id,
                    'customer_type' => 'residential',
                    'outstanding_financial_balance' => 0.00,
                    'credit_limit' => 500.00,
                    'allow_pay_later' => false,
                    'referral_code' => 'ANAND42',
                ]
            );

            CustomerAddress::firstOrCreate(
                ['customer_profile_id' => $customerProfile->id, 'street_address' => 'Flat 3B, Sunshine Apartments, 12th Cross Street'],
                [
                    'address_type' => 'home',
                    'contact_person_name' => 'Anand Krishnamurthy',
                    'contact_person_mobile' => '9876543213',
                    'house_flat_no' => 'Flat 3B',
                    'building_apartment_name' => 'Sunshine Apartments',
                    'landmark' => 'Near Guindy Metro Station',
                    'area' => 'Guindy',
                    'city' => 'Chennai',
                    'state' => 'Tamil Nadu',
                    'pincode' => '600032',
                    'delivery_instructions' => 'Call before delivery. Lift is operational.',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }

        // 5. Test Corporate B2B Customer (Pay Later enabled)
        $corporateUser = User::firstOrCreate(
            ['mobile' => '9876543214'],
            [
                'name' => 'Priya Nair (Office Admin)',
                'email' => 'admin@innovatetech.io',
                'password' => Hash::make('password123'),
                'user_type' => 'customer',
                'is_active' => true,
            ]
        );
        if ($custRole) {
            $corporateUser->roles()->syncWithoutDetaching([$custRole->id]);
        }
        if ($branch) {
            $corporateProfile = CustomerProfile::firstOrCreate(
                ['user_id' => $corporateUser->id],
                [
                    'branch_id' => $branch->id,
                    'customer_type' => 'commercial',
                    'company_name' => 'InnovateTech Systems LLP',
                    'gst_number' => '33AABCI9999K1Z2',
                    'outstanding_financial_balance' => 0.00,
                    'credit_limit' => 5000.00,
                    'allow_pay_later' => true,
                    'referral_code' => 'INNOVATE',
                ]
            );

            CustomerAddress::firstOrCreate(
                ['customer_profile_id' => $corporateProfile->id, 'street_address' => '4th Floor, Olympia Tech Park'],
                [
                    'address_type' => 'office',
                    'contact_person_name' => 'Priya Nair (Admin Desk)',
                    'contact_person_mobile' => '9876543214',
                    'house_flat_no' => 'Tower C, 4th Floor',
                    'building_apartment_name' => 'Olympia Technology Park',
                    'landmark' => 'SIDCO Industrial Estate',
                    'area' => 'Guindy',
                    'city' => 'Chennai',
                    'state' => 'Tamil Nadu',
                    'pincode' => '600032',
                    'delivery_instructions' => 'Deliver between 9 AM and 5 PM at Service Lift Bay.',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
