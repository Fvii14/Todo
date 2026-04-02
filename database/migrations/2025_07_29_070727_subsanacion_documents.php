<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subsanacion_documents', function (Blueprint $table) {
            $table->id();

            // Relación con la contratación
            $table->unsignedBigInteger('contratacion_id');
            $table->foreign('contratacion_id')->references('id')->on('contrataciones')->onDelete('cascade');

            // Documento solicitado (referencia a documents o ayuda_documentos)
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            // Quién lo solicitó (opcional)
            $table->unsignedBigInteger('solicitado_por')->nullable();
            $table->foreign('solicitado_por')->references('id')->on('users')->nullOnDelete();

            // Estado del documento
            $table->enum('estado', ['pendiente', 'subido', 'validado', 'rechazado'])->default('pendiente');

            // Motivo del rechazo (solo si estado = rechazado)
            $table->enum('motivo_rechazo', [
                'ilegible',
                'incorrecto',
                'no_valido',
                'caducado',
                'faltan_paginas',
                'personalizado',
            ])->nullable();

            // Nota personalizada opcional
            $table->text('nota_personalizada')->nullable();

            // Fecha en que se solicitó
            $table->timestamp('fecha_solicitado')->useCurrent();

            // Por si queremos registrar cuándo se completó
            $table->timestamp('fecha_completado')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsanacion_documents');
    }
};
