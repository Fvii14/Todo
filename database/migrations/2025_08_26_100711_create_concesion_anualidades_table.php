<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concesion_anualidades', function (Blueprint $table) {
            $table->id();

            // Relación con la contratación
            $table->unsignedBigInteger('contratacion_id');

            // Año y cantidad asignada a ese año
            $table->unsignedSmallInteger('anio');          // p.ej. 2024, 2025
            $table->decimal('importe', 10, 2);             // importe anual asignado

            $table->timestamps();

            // FK + cascada para mantener integridad
            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->onUpdate('cascade')->onDelete('cascade');

            // Un año por contratación
            $table->unique(['contratacion_id', 'anio'], 'uniq_contratacion_anio');

            // Búsquedas por año (informes)
            $table->index('anio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concesion_anualidades');
    }
};
