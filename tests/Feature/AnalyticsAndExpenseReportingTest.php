<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsAndExpenseReportingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_expense_tracker_and_record_new_expense(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $category = ExpenseCategory::first();
        $branch = Branch::first();

        $response = $this->actingAs($admin)->get(route('admin.expenses.index'));
        $response->assertStatus(200);
        $response->assertSee('Operating Expenses');

        // Record a new expense
        $postResponse = $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'branch_id' => $branch->id,
            'expense_category_id' => $category->id,
            'title' => 'Diesel fuel for delivery truck',
            'amount' => 2450.00,
            'expense_date' => Carbon::today()->format('Y-m-d'),
            'payment_method' => 'cash',
            'paid_to' => 'BPCL Petrol Bunk',
            'reference_invoice_no' => 'BILL-88741',
            'notes' => 'Full tank top-up for northern delivery route',
        ]);

        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'title' => 'Diesel fuel for delivery truck',
            'amount' => 2450.00,
            'payment_method' => 'cash',
            'paid_to' => 'BPCL Petrol Bunk',
        ]);

        $expense = Expense::latest('id')->first();
        $this->assertStringStartsWith('EXP-' . date('Y') . '-', $expense->expense_number);
    }

    public function test_admin_can_delete_expense(): void
    {
        $admin = User::where('mobile', '9876543210')->first();
        $category = ExpenseCategory::first();
        $branch = Branch::first();

        $expense = Expense::create([
            'expense_number' => 'EXP-2026-9999',
            'branch_id' => $branch->id,
            'expense_category_id' => $category->id,
            'title' => 'Incorrect test expense',
            'amount' => 500.00,
            'expense_date' => Carbon::today(),
            'payment_method' => 'cash',
            'recorded_by_user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.expenses.destroy', $expense));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_admin_can_view_executive_analytics_dashboard(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Executive Business Analytics');
        $response->assertSee('Gross Sales Revenue');
        $response->assertSee('Net Operating Profit');
        $response->assertSee('Online Collected');
    }

    public function test_admin_can_view_pnl_statement(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.reports.pnl'));

        $response->assertStatus(200);
        $response->assertSee('Statement of Profit');
        $response->assertSee('Operating Revenue');
        $response->assertSee('Cost of Goods Sold');
        $response->assertSee('Administrative Expenses');
        $response->assertSee('Net Operating Profit');
    }

    public function test_admin_can_view_container_fleet_and_shrinkage_report(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.reports.containers'));

        $response->assertStatus(200);
        $response->assertSee('Container Fleet');
        $response->assertSee('Total Active Fleet');
        $response->assertSee('Customer Hands');
        $response->assertSee('In Warehouse Hub');
        $response->assertSee('Shrinkage / Loss Rate');
    }

    public function test_admin_can_view_fleet_and_driver_efficiency_report(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.reports.fleet'));

        $response->assertStatus(200);
        $response->assertSee('Fleet Efficiency');
        $response->assertSee('Total Runsheets');
        $response->assertSee('Fulfillment Rate');
    }

    public function test_admin_can_export_orders_csv(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.reports.export-orders'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Order Number', $content);
        $this->assertStringContainsString('Customer Mobile', $content);
    }

    public function test_admin_can_export_expenses_csv(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.reports.export-expenses'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Expense Number', $content);
        $this->assertStringContainsString('Amount (INR)', $content);
    }

    public function test_admin_can_export_customer_dues_csv(): void
    {
        $admin = User::where('mobile', '9876543210')->first();

        $response = $this->actingAs($admin)->get(route('admin.reports.export-dues'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Customer Name', $content);
        $this->assertStringContainsString('Outstanding Balance (INR)', $content);
    }

    public function test_unauthorized_customer_cannot_access_analytics_or_expenses(): void
    {
        $customerUser = User::where('mobile', '9876543213')->first();

        $this->actingAs($customerUser)->get(route('admin.reports.index'))->assertStatus(403);
        $this->actingAs($customerUser)->get(route('admin.reports.pnl'))->assertStatus(403);
        $this->actingAs($customerUser)->get(route('admin.expenses.index'))->assertStatus(403);
    }
}
