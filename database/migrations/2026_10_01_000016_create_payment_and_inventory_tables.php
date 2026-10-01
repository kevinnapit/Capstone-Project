<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->restrictOnDelete();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('paid');
            $table->string('reference_number', 100)->nullable();
            $table->dateTime('paid_at');
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->index(['order_id', 'paid_at']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->enum('movement_type', ['purchase', 'sale', 'service_usage', 'rework', 'waste', 'adjustment', 'opname']);
            $table->enum('direction', ['in', 'out']);
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('stock_before', 15, 2);
            $table->decimal('stock_after', 15, 2);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('notes', 500)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('occurred_at');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'occurred_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('order_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->enum('type', ['revision', 'cancellation']);
            $table->text('reason');
            $table->decimal('charge_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_adjustments');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('payments');
    }
};
