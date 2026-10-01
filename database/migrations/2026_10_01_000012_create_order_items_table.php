<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->enum('type', ['product', 'service']);
            $table->foreignId('product_id')->nullable()->constrained('products')->restrictOnDelete();
            $table->foreignId('service_price_id')->nullable()->constrained('service_prices')->restrictOnDelete();
            $table->string('item_name_snapshot', 150);
            $table->decimal('quantity_billed', 15, 2);
            $table->decimal('unit_price_snapshot', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
