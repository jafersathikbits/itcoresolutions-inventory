<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // Daily report date (one report per day)
            $table->date('report_date')->unique();

            // storage path like: reports/2026-01-30-daily-report.pdf
            $table->string('file_path');

            // When report was generated
            $table->timestamp('generated_at')->useCurrent();

            $table->timestamps();

            $table->index(['report_date', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
