<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaAndPerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_manifest_json_is_valid_and_accessible(): void
    {
        $response = $this->get('/manifest.json');

        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertEquals('AquaFlow Water Delivery', $data['name']);
        $this->assertEquals('AquaFlow', $data['short_name']);
        $this->assertEquals('#0284c7', $data['theme_color']);
        $this->assertEquals('standalone', $data['display']);
        $this->assertNotEmpty($data['icons']);
        $this->assertNotEmpty($data['shortcuts']);
    }

    public function test_service_worker_script_is_accessible(): void
    {
        $response = $this->get('/sw.js');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('CACHE_NAME', $content);
        $this->assertStringContainsString('OFFLINE_URL', $content);
        $this->assertStringContainsString('PRECACHE_ASSETS', $content);
        $this->assertStringContainsString('navigate', $content);
    }

    public function test_offline_fallback_page_is_rendered(): void
    {
        $response = $this->get(route('offline'));

        $response->assertStatus(200);
        $response->assertSee('Currently Offline');
        $response->assertSee('Retry Connection');
        $response->assertSee('Check Connection Steps');
        $response->assertSee('Emergency Dispatch: 1800-425-AQUA');
    }

    public function test_pwa_icons_exist_on_disk(): void
    {
        $this->assertFileExists(public_path('assets/icons/icon-144.png'));
        $this->assertFileExists(public_path('assets/icons/icon-192.png'));
        $this->assertFileExists(public_path('assets/icons/icon-512.png'));
        $this->assertFileExists(public_path('assets/icons/icon-maskable.png'));
        $this->assertFileExists(public_path('favicon.ico'));
    }

    public function test_landing_page_includes_pwa_manifest_and_apple_touch_icon(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('rel="manifest" href="/manifest.json"', false);
        $response->assertSee('rel="apple-touch-icon"', false);
        $response->assertSee('meta name="theme-color" content="#0284c7"', false);
        $response->assertSee('/assets/js/app.js', false);
    }

    public function test_htaccess_contains_gzip_compression_and_browser_caching_directives(): void
    {
        $htaccessPath = public_path('.htaccess');
        $this->assertFileExists($htaccessPath);

        $content = file_get_contents($htaccessPath);
        $this->assertStringContainsString('mod_deflate.c', $content);
        $this->assertStringContainsString('mod_expires.c', $content);
        $this->assertStringContainsString('X-Content-Type-Options "nosniff"', $content);
    }

    public function test_customer_portal_renders_with_pwa_smart_banner_and_bottom_nav(): void
    {
        $customer = \App\Models\User::where('mobile', '9876543213')->first();

        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Install AquaFlow Water App');
        $response->assertSee('mobile-bottom-nav', false);
        $response->assertSee('Can Passbook');
        $response->assertSee('Dues & Ledger', false);
        $response->assertSee('Subscribe');
        $response->assertSee('Install on iPhone / iPad');
    }
}
