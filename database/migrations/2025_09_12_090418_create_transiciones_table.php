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
        Schema::create('transiciones', function (Blueprint $table) {
            $table->id();

            // Estado origen y destino (pueden ser null si solo cambia la fase)
            $table->string('estado_origen', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('estado_destino', 255)->nullable()->collation('utf8mb4_general_ci');

            // Fase origen y destino (pueden ser null si solo cambia el estado)
            $table->string('fase_origen', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('fase_destino', 255)->nullable()->collation('utf8mb4_general_ci');

            // Tipo de transición: 'estado', 'fase', 'ambos'
            $table->enum('tipo', ['estado', 'fase', 'ambos'])->default('estado');

            // Descripción opcional de la transición
            $table->string('descripcion', 500)->nullable();

            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index(['estado_origen', 'estado_destino']);
            $table->index(['fase_origen', 'fase_destino']);
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transiciones');
    }
};
