<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained('service_types')->restrictOnDelete();
            $table->foreignId('paper_type_id')->constrained('paper_types')->restrictOnDelete();
            $table->foreignId('print_mode_id')->nullable()->constrained('print_modes')->restrictOnDelete();
            $table->enum('side_mode', ['none', 'single_sided', 'duplex'])->default('none');
            $table->decimal('price', 15, 2);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(
                ['service_type_id', 'paper_type_id', 'print_mode_id', 'side_mode', 'effective_from'],
                'service_prices_variant_effective_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
