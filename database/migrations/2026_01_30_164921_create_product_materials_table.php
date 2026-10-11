<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();

            // Qty required for producing 1 unit of product
            $table->decimal('qty_per_product', 12, 3);

            $table->timestamps();

            $table->unique(['product_id', 'material_id']);
            $table->index(['product_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_materials');
    }
};
