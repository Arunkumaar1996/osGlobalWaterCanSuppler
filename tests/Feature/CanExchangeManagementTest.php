<?php

namespace Tests\Feature;

use App\Models\CanMovement;
use App\Models\CanType;
use App\Models\CustomerCanBalance;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\CanExchangeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanExchangeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_customer_can_view_empty_can_container_passbook(): void
    {
        $customer = User::where('mobile', '9876543213')->first();

        $response = $this->actingAs($customer)->get(route('customer.cans.index'));

        $response->assertStatus(200);
        $response->assertSee('Empty-Can Exchange Balance');
        $response->assertSee('Total Containers Held');
        $response->assertSee('1:1 Can Exchange Works');
        $response->assertSee('Container Movement Passbook');
    }

    public function test_recording_1_to_1_delivery_exchange_maintains_net_holding_balance(): void
    {
        $customer = CustomerProfile::first();
        $canType = CanType::first();
        $service = app(CanExchangeService::class);

        // Customer initially holds 2 cans
        CustomerCanBalance::create([
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 2,
        ]);

        // Exchange 5 full delivered, 5 empty returned
        $movement = $service->recordExchange($customer, $canType, 5, 5);

        $this->assertEquals(2, $movement->balance_after_movement);
        $this->assertDatabaseHas('customer_can_balances', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 2,
        ]);
        $this->assertDatabaseHas('can_movements', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'movement_type' => 'delivery_exchange',
            'cans_delivered_full' => 5,
            'cans_collected_empty' => 5,
            'balance_after_movement' => 2,
        ]);
    }

    public function test_delivery_with_fewer_returned_empties_increases_customer_holding(): void
    {
        $customer = CustomerProfile::first();
        $canType = CanType::first();
        $service = app(CanExchangeService::class);

        // Initially 0 held
        $movement = $service->recordExchange($customer, $canType, 4, 1);

        $this->assertEquals(3, $movement->balance_after_movement);
        $this->assertDatabaseHas('customer_can_balances', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 3,
        ]);
    }

    public function test_customer_returning_extra_empties_decreases_container_holding(): void
    {
        $customer = CustomerProfile::first();
        $canType = CanType::first();
        $service = app(CanExchangeService::class);

        CustomerCanBalance::create([
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 5,
        ]);

        $movement = $service->recordExchange($customer, $canType, 0, 3);

        $this->assertEquals(2, $movement->balance_after_movement);
        $this->assertEquals('return_collection', $movement->movement_type);
        $this->assertDatabaseHas('customer_can_balances', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 2,
        ]);
    }

    public function test_admin_can_view_container_fleet_overview(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.cans.index'));

        $response->assertStatus(200);
        $response->assertSee('Empty-Can Exchanges &amp; Container Fleet', false);
        $response->assertSee('Containers in Circulation');
        $response->assertSee('Holding Customers');
    }

    public function test_admin_can_record_direct_empty_return_from_customer(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $customer = CustomerProfile::first();
        $canType = CanType::first();

        CustomerCanBalance::create([
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 4,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.cans.return', $customer->id), [
            'can_type_id' => $canType->id,
            'collected_empty' => 2,
            'notes' => 'Driver picked up 2 empties from security desk',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customer_can_balances', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 2,
        ]);
    }

    public function test_charging_missing_container_penalty_reduces_cans_and_debits_financial_ledger(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $customer = CustomerProfile::first();
        $canType = CanType::first(); // deposit = 150.00
        $customer->update(['outstanding_financial_balance' => 0.00]);

        CustomerCanBalance::create([
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 3,
        ]);

        // Charge penalty for 2 missing cans
        $response = $this->actingAs($admin)->post(route('admin.cans.penalty', $customer->id), [
            'can_type_id' => $canType->id,
            'missing_cans' => 2,
            'reason' => 'Containers lost during office shifting by tenant',
        ]);

        $response->assertSessionHas('success');

        // 1. Container balance reduced from 3 to 1
        $this->assertDatabaseHas('customer_can_balances', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 1,
        ]);

        // 2. Can movement logged
        $this->assertDatabaseHas('can_movements', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'movement_type' => 'missing_penalty',
            'missing_cans' => 2,
            'balance_after_movement' => 1,
        ]);

        // 3. Monetary financial ledger debited: 2 * 150 = ₹300
        $expectedPenalty = 2 * (float) $canType->deposit_amount;
        $customer->refresh();
        $this->assertEquals($expectedPenalty, (float) $customer->outstanding_financial_balance);

        $this->assertDatabaseHas('customer_ledger', [
            'customer_profile_id' => $customer->id,
            'transaction_type' => 'debit_adjustment',
            'debit_amount' => $expectedPenalty,
            'running_balance' => $expectedPenalty,
        ]);
    }

    public function test_admin_physical_count_reconciliation_updates_balance_with_audit_trail(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $customer = CustomerProfile::first();
        $canType = CanType::first();

        CustomerCanBalance::create([
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 5,
        ]);

        // Reconcile to 7 cans after on-site audit
        $response = $this->actingAs($admin)->post(route('admin.cans.reconcile', $customer->id), [
            'can_type_id' => $canType->id,
            'actual_count' => 7,
            'notes' => 'Audited physical stock in basement pantry',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customer_can_balances', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 7,
        ]);

        $this->assertDatabaseHas('can_movements', [
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'movement_type' => 'audit_reconciliation',
            'balance_after_movement' => 7,
        ]);
    }

    public function test_customer_dashboard_reflects_real_time_container_count(): void
    {
        $customerUser = User::where('mobile', '9876543213')->first();
        $customer = $customerUser->customerProfile;
        $canType = CanType::first();

        CustomerCanBalance::create([
            'customer_profile_id' => $customer->id,
            'can_type_id' => $canType->id,
            'held_cans_count' => 4,
        ]);

        $response = $this->actingAs($customerUser)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('4');
        $response->assertSee('Bottles');
    }

    public function test_admin_can_view_container_physical_movements_audit_log(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.cans.movements'));

        $response->assertStatus(200);
        $response->assertSee('Container Physical Movements Audit Log');
        $response->assertSee('Movement Type');
        $response->assertSee('Delivered Full');
        $response->assertSee('Collected Empty');
    }
}
