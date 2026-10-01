<?php

namespace Tests\Feature;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderStatusFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
        $this->seed(DatabaseSeeder::class);
        $this->employee = User::factory()->create();
        $this->employee->assignRole('employee');
    }

    public function test_action_runs_complete_status_flow_and_records_history(): void
    {
        $order = Order::factory()->create(['created_by' => $this->employee]);
        $action = app(ChangeOrderStatusAction::class);

        $action->execute($order, $this->employee, OrderStatus::Confirmed);
        $action->execute($order, $this->employee, OrderStatus::Processing);
        $completed = $action->execute($order, $this->employee, OrderStatus::Completed);

        $this->assertSame(OrderStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertNull($completed->cancelled_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'processing',
            'to_status' => 'completed',
            'changed_by' => $this->employee->id,
        ]);
        $this->assertSame(3, OrderStatusHistory::query()->whereBelongsTo($order)->count());
    }

    public function test_action_rejects_illegal_transition_without_changing_order(): void
    {
        $order = Order::factory()->create(['created_by' => $this->employee]);

        try {
            app(ChangeOrderStatusAction::class)->execute($order, $this->employee, OrderStatus::Processing);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $this->assertSame(OrderStatus::Draft, $order->fresh()->status);
        $this->assertDatabaseCount('order_status_histories', 0);
    }

    public function test_employee_can_view_detail_and_cancel_with_a_reason(): void
    {
        $order = Order::factory()->create(['created_by' => $this->employee]);

        $this->actingAs($this->employee)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Ubah status');

        $this->actingAs($this->employee)
            ->patch(route('orders.status.update', $order), ['status' => 'cancelled'])
            ->assertSessionHasErrors('reason');

        $this->actingAs($this->employee)
            ->patch(route('orders.status.update', $order), [
                'status' => 'cancelled',
                'reason' => 'Pelanggan membatalkan pesanan.',
            ])
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->cancelled_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'cancelled',
            'reason' => 'Pelanggan membatalkan pesanan.',
        ]);
    }

    public function test_user_without_order_permissions_cannot_view_or_change_status(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['created_by' => $this->employee]);

        $this->actingAs($user)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($user)->patch(route('orders.status.update', $order), [
            'status' => 'confirmed',
        ])->assertForbidden();
    }
}
