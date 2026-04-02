<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();           // p.ej. TTF-2025-000123
            $table->date('fecha_emision')->nullable();
            $table->decimal('importe_total', 10, 2)->nullable();
            $table->string('pdf_path')->nullable();       // ruta en GCS u otro disco
            $table->timestamps();

            $table->index('fecha_emision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
