<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_item_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->unique()->constrained('order_items')->restrictOnDelete();
            $table->foreignId('paper_type_id')->constrained('paper_types')->restrictOnDelete();
            $table->unsignedInteger('pages')->default(1);
            $table->unsignedInteger('copies')->default(1);
            $table->unsignedInteger('sheets_billed')->default(0);
            $table->unsignedInteger('sheets_consumed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_item_details');
    }
};
