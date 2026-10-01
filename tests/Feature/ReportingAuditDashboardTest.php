<?php

namespace Tests\Feature;

use App\Actions\Inventory\AdjustStockAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingAuditDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_dashboard_displays_live_operational_statistics(): void
    {
        Order::factory()->create(['status' => OrderStatus::Processing, 'ordered_at' => now()]);
        Order::factory()->create(['status' => OrderStatus::Completed, 'ordered_at' => now(), 'completed_at' => now(), 'grand_total' => 15000, 'paid_amount' => 15000]);
        Product::factory()->create(['name' => 'Stok Kritis', 'current_stock' => 2, 'minimum_stock' => 5]);

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Penjualan Hari Ini')
            ->assertSee('15.000')
            ->assertSee('Stok Kritis');
    }

    public function test_sales_report_only_counts_completed_orders_in_period(): void
    {
        $completed = Order::factory()->create(['status' => OrderStatus::Completed, 'completed_at' => now(), 'grand_total' => 20000, 'paid_amount' => 20000]);
        Order::factory()->create(['status' => OrderStatus::Processing, 'grand_total' => 90000]);
        $method = PaymentMethod::query()->where('is_active', true)->firstOrFail();
        Payment::query()->create(['order_id' => $completed->id, 'payment_method_id' => $method->id, 'received_by' => $this->admin->id, 'amount' => 20000, 'status' => 'paid', 'paid_at' => now()]);

        $this->actingAs($this->admin)->get(route('reports.sales', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]))
            ->assertOk()
            ->assertSee($completed->order_number)
            ->assertSee('20.000')
            ->assertDontSee('90.000');
    }

    public function test_important_actions_are_written_to_append_only_audit_log(): void
    {
        $product = Product::factory()->create(['current_stock' => 10]);
        $this->actingAs($this->admin);

        app(AdjustStockAction::class)->execute($product, $this->admin, 'out', 2, 'Koreksi hasil opname.');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'stock.adjusted',
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
        ]);
        $this->get(route('audit-logs.index'))->assertOk()->assertSee('stock.adjusted')->assertSee('Koreksi hasil opname.');
    }

    public function test_employee_cannot_access_owner_reports_or_audit_logs(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $this->actingAs($employee)->get(route('reports.sales'))->assertForbidden();
        $this->actingAs($employee)->get(route('audit-logs.index'))->assertForbidden();
    }
}
