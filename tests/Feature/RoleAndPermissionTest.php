<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Operations Dashboard');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::where('mobile', '9876543213')->first();

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_staff_can_access_staff_deliveries_dashboard(): void
    {
        $staff = User::where('mobile', '9876543212')->first();

        $response = $this->actingAs($staff)->get(route('staff.deliveries'));

        $response->assertStatus(200);
        $response->assertSee('Assigned Deliveries');
    }

    public function test_customer_can_access_customer_dashboard(): void
    {
        $customer = User::where('mobile', '9876543213')->first();

        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome, ' . $customer->name);
    }
}
