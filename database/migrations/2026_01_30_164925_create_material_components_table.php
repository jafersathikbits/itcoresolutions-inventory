<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('material_components', function (Blueprint $table) {
            $table->id();

            // Parent composite material (e.g., trays)
            $table->foreignId('parent_material_id')
                ->constrained('materials')
                ->cascadeOnDelete();

            // Child raw material (e.g., Aluminium L angle 3/4, stainless net)
            $table->foreignId('child_material_id')
                ->constrained('materials')
                ->restrictOnDelete();

            // Quantity of child needed to produce 1 unit of parent
            $table->decimal('qty_per_parent', 12, 3);

            $table->timestamps();

            // Prevent duplicates: same child repeated for same parent
            $table->unique(['parent_material_id', 'child_material_id']);

            $table->index(['parent_material_id', 'child_material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_components');
    }
};
