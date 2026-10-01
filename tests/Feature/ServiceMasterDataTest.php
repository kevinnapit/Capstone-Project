<?php

namespace Tests\Feature;

use App\Enums\SideMode;
use App\Models\PaperType;
use App\Models\PrintMode;
use App\Models\Product;
use App\Models\ServicePrice;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ServiceMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_service_master_seeder_contains_initial_prices_and_is_idempotent(): void
    {
        $this->seed(ServiceMasterDataSeeder::class);

        $this->assertDatabaseCount('paper_types', 5);
        $this->assertDatabaseCount('service_types', 2);
        $this->assertDatabaseCount('print_modes', 3);
        $this->assertDatabaseCount('service_prices', 16);

        $a4 = PaperType::where('code', 'A4')->firstOrFail();
        $photocopy = ServiceType::where('code', 'PHOTOCOPY')->firstOrFail();
        $this->assertDatabaseHas('service_prices', [
            'service_type_id' => $photocopy->id,
            'paper_type_id' => $a4->id,
            'side_mode' => SideMode::SingleSided->value,
            'price' => 200,
        ]);
    }

    public function test_admin_can_render_all_service_master_pages(): void
    {
        $price = ServicePrice::firstOrFail();
        $paperType = PaperType::firstOrFail();

        $this->actingAs($this->admin)->get(route('paper-types.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('paper-types.edit', $paperType))->assertOk();
        $this->actingAs($this->admin)->get(route('service-types.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('print-modes.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('service-prices.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('service-prices.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('service-prices.edit', $price))->assertOk();
    }

    public function test_duplicate_service_price_is_rejected_and_deletion_archives_price(): void
    {
        $price = ServicePrice::whereHas('serviceType', fn ($query) => $query->where('code', 'PHOTOCOPY'))->firstOrFail();

        $this->actingAs($this->admin)->post(route('service-prices.store'), [
            'service_type_id' => $price->service_type_id,
            'paper_type_id' => $price->paper_type_id,
            'print_mode_id' => '',
            'side_mode' => $price->side_mode->value,
            'price' => 999,
            'effective_from' => $price->effective_from->format('Y-m-d'),
            'effective_until' => '',
            'is_active' => 1,
        ])->assertSessionHasErrors('effective_from');

        $this->actingAs($this->admin)->delete(route('service-prices.destroy', $price))->assertRedirect(route('service-prices.index'));

        $price->refresh();
        $this->assertFalse($price->is_active);
        $this->assertNotNull($price->effective_until);
    }

    public function test_admin_can_manage_paper_service_mode_and_price(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)->post(route('paper-types.store'), [
            'inventory_product_id' => $product->id,
            'code' => 'legal',
            'name' => 'Kertas Legal',
            'is_active' => 1,
        ])->assertRedirect(route('paper-types.index'));

        $paper = PaperType::where('code', 'LEGAL')->firstOrFail();
        $this->actingAs($this->admin)->put(route('paper-types.update', $paper), [
            'inventory_product_id' => $product->id,
            'code' => 'legal',
            'name' => 'Legal',
            'is_active' => 1,
        ])->assertRedirect(route('paper-types.index'));

        $this->actingAs($this->admin)->post(route('service-types.store'), ['code' => 'scan', 'name' => 'Pindai', 'is_active' => 1])
            ->assertRedirect(route('service-types.index'));
        $service = ServiceType::where('code', 'SCAN')->firstOrFail();

        $this->actingAs($this->admin)->post(route('print-modes.store'), ['code' => 'draft', 'name' => 'Draf', 'is_active' => 1])
            ->assertRedirect(route('print-modes.index'));
        $mode = PrintMode::where('code', 'DRAFT')->firstOrFail();

        $this->actingAs($this->admin)->post(route('service-prices.store'), [
            'service_type_id' => $service->id,
            'paper_type_id' => $paper->id,
            'print_mode_id' => $mode->id,
            'side_mode' => SideMode::None->value,
            'price' => 2500,
            'effective_from' => '2026-09-29',
            'is_active' => 1,
        ])->assertRedirect(route('service-prices.index'));

        $price = ServicePrice::where('service_type_id', $service->id)->firstOrFail();
        $this->actingAs($this->admin)->put(route('service-prices.update', $price), [
            'service_type_id' => $service->id,
            'paper_type_id' => $paper->id,
            'print_mode_id' => $mode->id,
            'side_mode' => SideMode::None->value,
            'price' => 3000,
            'effective_from' => '2026-09-29',
            'is_active' => 1,
        ])->assertRedirect(route('service-prices.index'));

        $this->assertSame('3000.00', $price->fresh()->price);
    }

    public function test_employee_cannot_access_service_master_data(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $this->actingAs($employee)->get(route('paper-types.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('service-prices.index'))->assertForbidden();
    }
}
