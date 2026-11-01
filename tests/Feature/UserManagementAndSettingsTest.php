<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('mobile', '9876543210')->first();
        $this->customer = User::where('mobile', '9876543213')->first();
        $this->branch = Branch::first();
    }

    public function test_admin_can_view_user_directory_and_stats(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management & Access Control', false);
        $response->assertSee('Total Accounts');
        $response->assertSee($this->admin->name);
    }

    public function test_admin_can_filter_users_by_user_type(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['user_type' => 'customer']));

        $response->assertStatus(200);
        $response->assertSee($this->customer->name);
    }

    public function test_customer_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_permissions_matrix(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.permissions'));

        $response->assertStatus(200);
        $response->assertSee('Role-Based Access Control (RBAC) Matrix');
        $response->assertSee('Super Admin');
        $response->assertSee('orders.view');
        $response->assertSee('can_exchange.view');
    }

    public function test_admin_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('Provision New User Account');
        $response->assertSee('Access Roles & Permission Profiles', false);
    }

    public function test_admin_can_provision_new_staff_driver_account(): void
    {
        $deliveryStaffRole = Role::where('name', 'Delivery Staff')->first() ?? Role::first();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Karthik Subramanian',
            'mobile' => '9888877777',
            'email' => 'karthik@aquaflow.com',
            'password' => 'secret123',
            'user_type' => 'staff',
            'role_ids' => [$deliveryStaffRole->id],
            'branch_id' => $this->branch->id,
            'designation' => 'Lead Delivery Driver',
            'vehicle_number' => 'TN-07-CD-9988',
            'driving_license_no' => 'DL-99887766',
            'emergency_contact' => '9840011223',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Karthik Subramanian',
            'mobile' => '9888877777',
            'user_type' => 'staff',
            'is_active' => 1,
        ]);

        $newUser = User::where('mobile', '9888877777')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue(Hash::check('secret123', $newUser->password));
        $this->assertTrue($newUser->roles->contains('id', $deliveryStaffRole->id));
        $this->assertTrue($newUser->branches->contains('id', $this->branch->id));

        $this->assertDatabaseHas('staff_profiles', [
            'user_id' => $newUser->id,
            'branch_id' => $this->branch->id,
            'designation' => 'Lead Delivery Driver',
            'vehicle_number' => 'TN-07-CD-9988',
        ]);
    }

    public function test_admin_can_view_user_profile(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $this->customer));

        $response->assertStatus(200);
        $response->assertSee($this->customer->name);
        $response->assertSee('Customer Profile & Accounts Overview', false);
    }

    public function test_admin_can_edit_and_update_user(): void
    {
        $role = Role::first();

        $editPage = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->customer));
        $editPage->assertStatus(200);
        $editPage->assertSee('Modify User: ' . $this->customer->name);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $this->customer), [
            'name' => 'Updated Customer Name',
            'mobile' => $this->customer->mobile,
            'email' => 'updated_customer@test.com',
            'user_type' => 'customer',
            'role_ids' => [$role->id],
            'branch_id' => $this->branch->id,
            'is_active' => 1,
            'credit_limit' => 1200.00,
            'allow_pay_later' => 1,
        ]);

        $updateResponse->assertRedirect(route('admin.users.show', $this->customer));

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'name' => 'Updated Customer Name',
            'email' => 'updated_customer@test.com',
        ]);

        $this->assertDatabaseHas('customer_profiles', [
            'user_id' => $this->customer->id,
            'credit_limit' => 1200.00,
            'allow_pay_later' => 1,
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $target = User::where('id', '!=', $this->admin->id)->first();
        $initialStatus = $target->is_active;

        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $target));

        $response->assertRedirect();
        $this->assertEquals(!$initialStatus, $target->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->admin));

        $response->assertSessionHasErrors(['error']);
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_admin_can_view_settings_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('System & Branch Settings', false);
        $response->assertSee('Hub & Fulfillment Center Profile', false);
        $response->assertSee('Platform Diagnostics');
        $response->assertSee('Container Security & Empty Can Exchange Policy', false);
    }

    public function test_customer_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_branch_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.branch.update', $this->branch), [
            'name' => 'Chennai Prime Tech Hub',
            'code' => 'CHN-PRIME-01',
            'phone' => '+91 99999 88888',
            'email' => 'hub@aquaflow.com',
            'address' => 'Plot 99, OMR IT Highway',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600096',
            'charge_new_customer_deposit' => '1',
            'new_customer_deposit_amount' => '175.00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'id' => $this->branch->id,
            'name' => 'Chennai Prime Tech Hub',
            'code' => 'CHN-PRIME-01',
            'pincode' => '600096',
            'charge_new_customer_deposit' => 1,
            'new_customer_deposit_amount' => 175.00,
        ]);
    }

    public function test_admin_can_trigger_system_cache_and_storage_link(): void
    {
        $storageResp = $this->actingAs($this->admin)->post(route('admin.settings.storage-link'));
        $storageResp->assertRedirect();
        $storageResp->assertSessionHas('success');

        $cacheResp = $this->actingAs($this->admin)->post(route('admin.settings.optimize-clear'));
        $cacheResp->assertRedirect();
        $cacheResp->assertSessionHas('success');
    }

    public function test_pnl_report_loads_without_transaction_date_error(): void
    {
        // Tests the AnalyticsService fix where transaction_date was replaced with created_at
        $response = $this->actingAs($this->admin)->get(route('admin.reports.pnl'));

        $response->assertStatus(200);
        $response->assertSee('P&L Statement', false);
        $response->assertSee('Total Operating Income');
    }
}
