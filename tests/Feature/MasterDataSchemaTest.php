<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterDataSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_master_tables_have_the_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('product_categories', ['id', 'name', 'description']));
        $this->assertTrue(Schema::hasColumns('units', ['id', 'name', 'symbol']));
        $this->assertTrue(Schema::hasColumns('products', [
            'id', 'category_id', 'unit_id', 'sku', 'name', 'purchase_price', 'selling_price',
            'current_stock', 'minimum_stock', 'is_active',
        ]));
    }

    public function test_product_relations_and_decimal_casts_work(): void
    {
        $product = Product::factory()->create([
            'purchase_price' => 1000,
            'selling_price' => 1500,
        ]);

        $this->assertInstanceOf(ProductCategory::class, $product->category);
        $this->assertInstanceOf(Unit::class, $product->unit);
        $this->assertSame('1000.00', $product->purchase_price);
        $this->assertSame('1500.00', $product->selling_price);
    }

    public function test_master_data_seeder_is_idempotent(): void
    {
        $this->seed(MasterDataSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->assertDatabaseCount('product_categories', 2);
        $this->assertDatabaseCount('units', 4);
    }
}
