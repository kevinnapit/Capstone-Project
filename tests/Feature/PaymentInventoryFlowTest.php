<?php

namespace Tests\Feature;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Actions\Orders\RecordPaymentAction;
use App\Enums\OrderItemType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ServicePrice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentInventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->employee = User::factory()->create();
        $this->employee->assignRole('employee');
    }

    public function test_partial_payment_then_completion_reduces_product_stock(): void
    {
        $product = Product::factory()->create(['current_stock' => 10, 'selling_price' => 500]);
        $order = Order::factory()->create([
            'created_by' => $this->employee,
            'status' => OrderStatus::Processing,
            'subtotal' => 1000,
            'grand_total' => 1000,
            'paid_amount' => 0,
        ]);
        $order->items()->create([
            'type' => OrderItemType::Product,
            'product_id' => $product->id,
            'item_name_snapshot' => $product->name,
            'quantity_billed' => 2,
            'unit_price_snapshot' => 500,
            'subtotal' => 1000,
        ]);
        $method = PaymentMethod::query()->where('is_active', true)->firstOrFail();
        $payments = app(RecordPaymentAction::class);

        $payments->execute($order, $this->employee, ['payment_method_id' => $method->id, 'amount' => 400]);
        $this->assertSame('partial', $order->fresh()->paymentStatus());

        try {
            app(ChangeOrderStatusAction::class)->execute($order, $this->employee, OrderStatus::Completed);
            $this->fail('An unpaid order should not complete.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $payments->execute($order, $this->employee, ['payment_method_id' => $method->id, 'amount' => 600]);
        app(ChangeOrderStatusAction::class)->execute($order, $this->employee, OrderStatus::Completed);

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame('8.00', $product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity' => 2,
            'stock_before' => 10,
            'stock_after' => 8,
        ]);
    }

    public function test_payment_cannot_exceed_balance(): void
    {
        $order = Order::factory()->create(['created_by' => $this->employee, 'grand_total' => 1000]);
        $method = PaymentMethod::query()->where('is_active', true)->firstOrFail();

        $this->expectException(ValidationException::class);
        app(RecordPaymentAction::class)->execute($order, $this->employee, [
            'payment_method_id' => $method->id,
            'amount' => 1001,
        ]);
    }

    public function test_cancellation_records_used_paper_as_waste(): void
    {
        $price = ServicePrice::query()->with('paperType.inventoryProduct')->firstOrFail();
        $price->paperType->inventoryProduct->update(['current_stock' => 20]);
        $order = Order::factory()->create(['created_by' => $this->employee]);
        $item = $order->items()->create([
            'type' => OrderItemType::Service,
            'service_price_id' => $price->id,
            'item_name_snapshot' => 'Fotokopi A4',
            'quantity_billed' => 10,
            'unit_price_snapshot' => $price->price,
            'subtotal' => (float) $price->price * 10,
        ]);
        $item->serviceDetail()->create([
            'paper_type_id' => $price->paper_type_id,
            'pages' => 10,
            'copies' => 1,
            'sheets_billed' => 10,
            'sheets_consumed' => 0,
        ]);

        app(ChangeOrderStatusAction::class)->execute(
            $order,
            $this->employee,
            OrderStatus::Cancelled,
            'Pelanggan membatalkan setelah proses dimulai.',
            [$item->id => 3]
        );

        $this->assertSame('17.00', $price->paperType->inventoryProduct->fresh()->current_stock);
        $this->assertSame(3, $item->serviceDetail->fresh()->sheets_consumed);
        $this->assertDatabaseHas('stock_movements', ['reference_id' => $item->id, 'movement_type' => 'waste', 'quantity' => 3]);
        $this->assertDatabaseHas('order_adjustments', ['order_id' => $order->id, 'type' => 'cancellation']);
    }

    public function test_employee_can_record_payment_from_order_detail(): void
    {
        $order = Order::factory()->create(['created_by' => $this->employee, 'grand_total' => 1000]);
        $method = PaymentMethod::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($this->employee)->post(route('orders.payments.store', $order), [
            'payment_method_id' => $method->id,
            'amount' => 500,
            'reference_number' => 'TRX-001',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertSame('500.00', $order->fresh()->paid_amount);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'reference_number' => 'TRX-001']);
    }
}
