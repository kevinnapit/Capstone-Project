<?php

namespace Tests\Feature;

use App\Actions\Orders\CreateDraftOrderAction;
use App\Enums\OrderItemType;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ServicePrice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_foundation_tables_have_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('customers', ['id', 'name', 'phone', 'address']));
        $this->assertTrue(Schema::hasColumns('orders', [
            'id', 'order_number', 'customer_id', 'channel_id', 'created_by', 'status',
            'ordered_at', 'subtotal', 'discount_amount', 'grand_total', 'paid_amount',
            'notes', 'completed_at', 'cancelled_at',
        ]));
        $this->assertTrue(Schema::hasColumns('order_items', [
            'id', 'order_id', 'type', 'product_id', 'service_price_id',
            'item_name_snapshot', 'quantity_billed', 'unit_price_snapshot', 'subtotal', 'notes',
        ]));
        $this->assertTrue(Schema::hasColumns('service_item_details', [
            'id', 'order_item_id', 'paper_type_id', 'pages', 'copies',
            'sheets_billed', 'sheets_consumed',
        ]));
    }

    public function test_order_relations_casts_and_snapshots_work(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['name' => 'Pulpen Lama', 'selling_price' => 3000]);
        $item = OrderItem::factory()->for($order)->for($product)->create([
            'item_name_snapshot' => $product->name,
            'unit_price_snapshot' => $product->selling_price,
            'quantity_billed' => 2,
            'subtotal' => 6000,
        ]);

        $product->update(['name' => 'Pulpen Baru', 'selling_price' => 4000]);

        $this->assertSame(OrderStatus::Draft, $order->status);
        $this->assertSame(OrderItemType::Product, $item->type);
        $this->assertSame('Pulpen Lama', $item->fresh()->item_name_snapshot);
        $this->assertSame('3000.00', $item->fresh()->unit_price_snapshot);
        $this->assertTrue($order->items->contains($item));
        $this->assertSame($order->id, $item->order->id);
    }

    public function test_customer_is_optional_but_related_customer_cannot_be_deleted(): void
    {
        $orderWithoutCustomer = Order::factory()->create(['customer_id' => null]);
        $this->assertNull($orderWithoutCustomer->customer);

        $customer = Customer::factory()->create();
        Order::factory()->for($customer)->create();

        $this->expectException(QueryException::class);
        $customer->delete();
    }

    public function test_order_status_transitions_are_centralized(): void
    {
        $this->assertTrue(OrderStatus::Draft->canTransitionTo(OrderStatus::Confirmed));
        $this->assertTrue(OrderStatus::Processing->canTransitionTo(OrderStatus::Completed));
        $this->assertFalse(OrderStatus::Completed->canTransitionTo(OrderStatus::Draft));
        $this->assertFalse(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Confirmed));
    }

    public function test_action_creates_mixed_draft_and_calculates_snapshots_on_server(): void
    {
        $user = User::factory()->create();
        $this->seed(DatabaseSeeder::class);
        $product = Product::factory()->create(['name' => 'Pulpen Biru', 'selling_price' => 3000, 'is_active' => true]);
        $servicePrice = ServicePrice::query()
            ->whereHas('serviceType', fn ($query) => $query->where('code', 'PHOTOCOPY'))
            ->whereHas('paperType', fn ($query) => $query->where('code', 'A4'))
            ->where('side_mode', 'duplex')
            ->firstOrFail();

        $order = app(CreateDraftOrderAction::class)->execute($user, [
            'channel_id' => $this->channelId('WALK_IN'),
            'customer_id' => null,
            'notes' => 'Pesanan campuran',
            'items' => [
                ['type' => 'product', 'product_id' => $product->id, 'quantity' => 2],
                ['type' => 'service', 'service_price_id' => $servicePrice->id, 'pages' => 3, 'copies' => 2],
            ],
        ]);

        $this->assertMatchesRegularExpression('/^ORD-\d{8}-0001$/', $order->order_number);
        $this->assertSame(OrderStatus::Draft, $order->status);
        $this->assertSame('7600.00', $order->subtotal);
        $this->assertSame('7600.00', $order->grand_total);
        $this->assertCount(2, $order->items);

        $productItem = $order->items->firstWhere('type', OrderItemType::Product);
        $serviceItem = $order->items->firstWhere('type', OrderItemType::Service);
        $this->assertSame('Pulpen Biru', $productItem->item_name_snapshot);
        $this->assertSame('3000.00', $productItem->unit_price_snapshot);
        $this->assertSame('4.00', $serviceItem->quantity_billed);
        $this->assertSame(3, $serviceItem->serviceDetail->pages);
        $this->assertSame(2, $serviceItem->serviceDetail->copies);
        $this->assertSame(4, $serviceItem->serviceDetail->sheets_billed);
        $this->assertSame(0, $serviceItem->serviceDetail->sheets_consumed);

        $product->update(['name' => 'Nama Berubah', 'selling_price' => 9000]);
        $this->assertSame('Pulpen Biru', $productItem->fresh()->item_name_snapshot);
        $this->assertSame('3000.00', $productItem->fresh()->unit_price_snapshot);
    }

    public function test_action_rolls_back_entire_draft_when_an_item_is_invalid(): void
    {
        $user = User::factory()->create();
        $this->seed(DatabaseSeeder::class);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);

        try {
            app(CreateDraftOrderAction::class)->execute($user, [
                'channel_id' => $this->channelId('WALK_IN'),
                'customer' => ['name' => 'Tidak Boleh Tersimpan'],
                'items' => [
                    ['type' => 'product', 'product_id' => $inactiveProduct->id, 'quantity' => 1],
                ],
            ]);
            $this->fail('ValidationException was not thrown.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('items', $exception->errors());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseMissing('customers', ['name' => 'Tidak Boleh Tersimpan']);
    }

    private function channelId(string $code): int
    {
        return \App\Models\OrderChannel::query()->where('code', $code)->value('id');
    }
}
