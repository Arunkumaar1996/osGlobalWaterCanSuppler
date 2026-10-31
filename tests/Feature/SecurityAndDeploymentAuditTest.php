<?php

namespace Tests\Feature;

use App\Models\CanMovement;
use App\Models\CustomerProfile;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SecurityAndDeploymentAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_root_htaccess_protects_env_and_source_directories(): void
    {
        $rootHtaccess = base_path('.htaccess');
        $this->assertFileExists($rootHtaccess);

        $content = file_get_contents($rootHtaccess);
        $this->assertStringContainsString('RewriteEngine On', $content);
        $this->assertStringContainsString('public/$1', $content);
        $this->assertStringContainsString('.env', $content);
        $this->assertStringContainsString('storage', $content);
    }

    public function test_admin_utilities_storage_link_and_optimize_clear_routes(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        // Storage Link Utility
        $linkResponse = $this->actingAs($admin)->post(route('admin.utilities.storage-link'));
        $linkResponse->assertSessionHas('success');

        // Optimize Clear Utility
        $clearResponse = $this->actingAs($admin)->post(route('admin.utilities.optimize-clear'));
        $clearResponse->assertSessionHas('success');
    }

    public function test_customer_cannot_access_admin_utilities(): void
    {
        $customerUser = User::where('mobile', '9876543213')->first();

        $this->actingAs($customerUser)
            ->post(route('admin.utilities.storage-link'))
            ->assertStatus(403);

        $this->actingAs($customerUser)
            ->post(route('admin.utilities.optimize-clear'))
            ->assertStatus(403);
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('customer.dashboard'))->assertRedirect(route('login'));
        $this->get(route('staff.deliveries'))->assertRedirect(route('login'));
    }

    public function test_mass_assignment_protection_on_core_models(): void
    {
        $models = [
            Order::class,
            Payment::class,
            Subscription::class,
            Expense::class,
            CustomerProfile::class,
            CanMovement::class,
        ];

        foreach ($models as $class) {
            $instance = new $class;
            $this->assertNotEmpty($instance->getFillable(), "{$class} must define fillable properties.");
        }
    }

    public function test_scheduled_subscriptions_command_is_registered_in_console(): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey('subscriptions:generate', $commands);

        $exitCode = Artisan::call('subscriptions:generate', ['--date' => date('Y-m-d')]);
        $this->assertEquals(0, $exitCode);
    }
}
