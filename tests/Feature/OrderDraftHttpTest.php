<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServicePrice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDraftHttpTest extends TestCase
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

    public function test_employee_can_render_order_list_and_create_form(): void
    {
        $this->actingAs($this->employee)->get(route('orders.index'))->assertOk();
        $this->actingAs($this->employee)->get(route('orders.create'))->assertOk();
    }

    public function test_employee_can_create_mixed_draft_with_new_customer_and_server_prices(): void
    {
        $product = Product::factory()->create(['name' => 'Pensil 2B', 'selling_price' => 2500, 'is_active' => true]);
        $servicePrice = ServicePrice::query()
            ->whereHas('serviceType', fn ($query) => $query->where('code', 'PHOTOCOPY'))
            ->whereHas('paperType', fn ($query) => $query->where('code', 'A4'))
            ->where('side_mode', 'single_sided')
            ->firstOrFail();

        $response = $this->actingAs($this->employee)->post(route('orders.store'), [
            'channel_id' => \App\Models\OrderChannel::where('code', 'WHATSAPP')->value('id'),
            'customer' => ['name' => 'Budi', 'phone' => '08123456789', 'address' => 'Jakarta'],
            'notes' => 'Ambil sore',
            'items' => [
                ['type' => 'product', 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1],
                ['type' => 'service', 'service_price_id' => $servicePrice->id, 'pages' => 3, 'copies' => 2, 'unit_price' => 1],
            ],
        ]);

        $order = Order::with(['customer', 'items.serviceDetail'])->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(OrderStatus::Draft, $order->status);
        $this->assertSame($this->employee->id, $order->created_by);
        $this->assertSame('Budi', $order->customer->name);
        $this->assertSame('6200.00', $order->grand_total);
        $this->assertSame('2500.00', $order->items->first()->unit_price_snapshot);
        $this->assertDatabaseHas('service_item_details', ['pages' => 3, 'copies' => 2, 'sheets_billed' => 6]);
    }

    public function test_request_rejects_empty_items_and_conflicting_customer_input(): void
    {
        $existingCustomer = \App\Models\Customer::factory()->create();

        $this->actingAs($this->employee)->post(route('orders.store'), [
            'channel_id' => \App\Models\OrderChannel::where('code', 'WALK_IN')->value('id'),
            'customer_id' => $existingCustomer->id,
            'customer' => ['name' => 'Pelanggan Baru'],
            'items' => [],
        ])->assertSessionHasErrors(['items', 'customer.name']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_user_without_order_permissions_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('orders.index'))->assertForbidden();
        $this->actingAs($user)->get(route('orders.create'))->assertForbidden();
        $this->actingAs($user)->post(route('orders.store'), [])->assertForbidden();
    }
}
