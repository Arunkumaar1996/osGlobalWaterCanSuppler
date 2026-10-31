<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign In to Your Account');
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Your Customer Account');
    }

    public function test_user_can_authenticate_using_mobile_number(): void
    {
        $response = $this->post('/login', [
            'login' => '9876543210',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_can_authenticate_using_email_address(): void
    {
        $response = $this->post('/login', [
            'login' => 'customer@gmail.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login' => '9876543210',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('mobile', '9876543213')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_new_customer_can_register_with_address(): void
    {
        $response = $this->post('/register', [
            'name' => 'Meena Sundaram',
            'mobile' => '9123456780',
            'email' => 'meena@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'house_flat_no' => 'Villa 7',
            'street_address' => 'Green Garden Avenue',
            'area' => 'Guindy',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600032',
            'delivery_instructions' => 'Gate code is 1234',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('customer.dashboard'));

        $this->assertDatabaseHas('users', [
            'mobile' => '9123456780',
            'email' => 'meena@example.com',
            'user_type' => 'customer',
        ]);

        $this->assertDatabaseHas('customer_profiles', [
            'customer_type' => 'residential',
        ]);

        $this->assertDatabaseHas('customer_addresses', [
            'house_flat_no' => 'Villa 7',
            'pincode' => '600032',
            'is_default' => 1,
        ]);
    }
}
