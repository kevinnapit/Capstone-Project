<?php

namespace Tests\Feature;

use App\Models\OrderChannel;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TransactionReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionReferenceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TransactionReferenceSeeder::class);
    }

    public function test_reference_seeder_is_idempotent(): void
    {
        $this->seed(TransactionReferenceSeeder::class);

        $this->assertDatabaseCount('order_channels', 2);
        $this->assertDatabaseCount('payment_methods', 3);
    }

    public function test_admin_can_render_and_manage_order_channels(): void
    {
        $this->actingAs($this->admin)->get(route('order-channels.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('order-channels.create'))->assertOk();

        $this->actingAs($this->admin)->post(route('order-channels.store'), [
            'code' => 'marketplace',
            'name' => 'Marketplace',
        ])->assertRedirect(route('order-channels.index'));

        $channel = OrderChannel::where('code', 'MARKETPLACE')->firstOrFail();
        $this->actingAs($this->admin)->put(route('order-channels.update', $channel), [
            'code' => 'marketplace',
            'name' => 'Pesanan Marketplace',
        ])->assertRedirect(route('order-channels.index'));

        $this->actingAs($this->admin)->delete(route('order-channels.destroy', $channel))
            ->assertRedirect(route('order-channels.index'));
        $this->assertModelMissing($channel);
    }

    public function test_admin_can_manage_and_archive_payment_methods(): void
    {
        $this->actingAs($this->admin)->get(route('payment-methods.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('payment-methods.create'))->assertOk();

        $this->actingAs($this->admin)->post(route('payment-methods.store'), [
            'code' => 'debit',
            'name' => 'Kartu debit',
            'is_active' => 1,
        ])->assertRedirect(route('payment-methods.index'));

        $method = PaymentMethod::where('code', 'DEBIT')->firstOrFail();
        $this->actingAs($this->admin)->delete(route('payment-methods.destroy', $method))
            ->assertRedirect(route('payment-methods.index'));

        $this->assertFalse($method->fresh()->is_active);
    }

    public function test_employee_cannot_access_transaction_reference_management(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $this->actingAs($employee)->get(route('order-channels.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('payment-methods.index'))->assertForbidden();
    }
}
