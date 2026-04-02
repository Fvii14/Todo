<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rechazos_contrataciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contratacion_id');
            // Lista de ids de motivos seleccionados (JSON)
            $table->json('motivo_ids')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->cascadeOnDelete();
        });

        // Eliminada tabla pivote: los motivos se guardan como JSON en rechazos_contrataciones
    }

    public function down(): void
    {
        Schema::dropIfExists('rechazos_contrataciones');
    }
};
