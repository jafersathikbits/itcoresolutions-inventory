<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();

            // Stock values (decimal to support sqft, ml, etc.)
            $table->decimal('stock', 12, 3)->default(0);
            $table->decimal('threshold', 12, 3)->default(0);

            // image stored under storage/app/public/...
            $table->string('image_path')->nullable();

            // If TRUE, this material is composite and has children in material_components
            $table->boolean('is_composite')->default(false);

            $table->timestamps();

            $table->index(['unit_id', 'is_composite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
