<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_landing_page_renders_with_dynamic_products(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pure Drinking Water,');
        $response->assertSee('20 Litre Premium Water Can');
        $response->assertSee('10 Litre Compact Water Can');
        $response->assertSee('5 Litre Portable Water Can');
    }

    public function test_landing_page_displays_how_it_works_steps(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('How AquaFlow Works in 4 Steps', false);
        $response->assertSee('Select your water can', false);
        $response->assertSee('Choose delivery date & time', false);
        $response->assertSee('Confirm your order', false);
        $response->assertSee('Receive water at doorstep', false);
    }

    public function test_landing_page_displays_faqs_and_testimonials(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Frequently Asked Questions', false);
        $response->assertSee('How do I order water cans online?', false);
        $response->assertSee('How does the empty-can exchange model work?', false);
        $response->assertSee('Loved by Residents & Corporates Alike', false);
    }

    public function test_pincode_checker_returns_success_for_serviceable_area(): void
    {
        $response = $this->postJson('/check-pincode', [
            'pincode' => '600032',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'serviceable' => true,
            'pincode' => '600032',
        ]);
        $response->assertJsonFragment([
            'area_name' => 'Guindy Industrial Estate',
        ]);
    }

    public function test_pincode_checker_returns_false_for_unserviced_area(): void
    {
        $response = $this->postJson('/check-pincode', [
            'pincode' => '110001',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'serviceable' => false,
            'pincode' => '110001',
        ]);
    }

    public function test_pincode_checker_validates_input(): void
    {
        $response = $this->postJson('/check-pincode', [
            'pincode' => 'invalid',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('pincode');
    }

    public function test_customer_can_submit_contact_inquiry(): void
    {
        $response = $this->postJson('/contact-inquiry', [
            'name' => 'Karthik Subramanian',
            'phone' => '9840123456',
            'email' => 'karthik@gmail.com',
            'subject' => 'Residential Delivery',
            'message' => 'Need 5 cans of 20L water every Tuesday and Friday morning in Guindy.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Karthik Subramanian',
            'phone' => '9840123456',
            'subject' => 'Residential Delivery',
            'status' => 'pending',
        ]);
    }
}
