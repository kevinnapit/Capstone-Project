<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_manage_categories_and_units(): void
    {
        $this->actingAs($this->admin)->get(route('categories.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('units.create'))->assertOk();

        $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Kertas',
            'description' => 'Bahan kertas',
        ])->assertRedirect(route('categories.index'));

        $this->actingAs($this->admin)->post(route('units.store'), [
            'name' => 'Lembar',
            'symbol' => 'LBR',
        ])->assertRedirect(route('units.index'));

        $this->assertDatabaseHas('product_categories', ['name' => 'Kertas']);
        $this->assertDatabaseHas('units', ['name' => 'Lembar', 'symbol' => 'lbr']);
    }

    public function test_admin_can_create_and_update_product_without_silently_changing_stock(): void
    {
        $category = ProductCategory::factory()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($this->admin)->get(route('products.create'))->assertOk();

        $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sku' => 'atk-001',
            'name' => 'Pulpen Biru',
            'purchase_price' => 2000,
            'selling_price' => 3000,
            'current_stock' => 10,
            'minimum_stock' => 2,
            'is_active' => 1,
        ])->assertRedirect(route('products.index'));

        $product = Product::query()->where('sku', 'ATK-001')->firstOrFail();

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sku' => 'atk-001',
            'name' => 'Pulpen Biru Baru',
            'purchase_price' => 2100,
            'selling_price' => 3200,
            'current_stock' => 999,
            'minimum_stock' => 3,
            'is_active' => 1,
        ])->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertSame('Pulpen Biru Baru', $product->name);
        $this->assertSame('10.00', $product->current_stock);
    }

    public function test_used_category_unit_and_stocked_product_cannot_be_deleted(): void
    {
        $product = Product::factory()->create(['current_stock' => 5]);

        $this->actingAs($this->admin)->delete(route('categories.destroy', $product->category))
            ->assertSessionHasErrors('category');
        $this->actingAs($this->admin)->delete(route('units.destroy', $product->unit))
            ->assertSessionHasErrors('unit');
        $this->actingAs($this->admin)->delete(route('products.destroy', $product))
            ->assertSessionHasErrors('product');

        $this->assertModelExists($product);
        $this->assertModelExists($product->category);
        $this->assertModelExists($product->unit);
    }

    public function test_employee_cannot_access_master_data(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $this->actingAs($employee)->get(route('products.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('units.index'))->assertForbidden();
    }
}
