<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_administracion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contratacion_id');

            // Nuevo: número secuencial del pago dentro de la misma contratación (1, 2, 3, ...)
            $table->unsignedSmallInteger('n_pago')->comment('Número secuencial del pago dentro de la contratación (1..n)');

            $table->decimal('importe_pagado', 10, 2);
            $table->date('fecha_pago')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->onUpdate('cascade')->onDelete('cascade');

            // Índices
            $table->index(['contratacion_id', 'fecha_pago']);
            $table->unique(['contratacion_id', 'n_pago'], 'uniq_contratacion_n_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_administracion');
    }
};
