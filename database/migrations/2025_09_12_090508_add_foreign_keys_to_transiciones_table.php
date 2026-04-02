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
        Schema::table('transiciones', function (Blueprint $table) {
            // Primero modificar las columnas para que coincidan con las de las tablas referenciadas
            // Usar la misma configuración que las tablas referenciadas (sin especificar collation para usar la por defecto)
            $table->string('estado_origen')->nullable()->change();
            $table->string('estado_destino')->nullable()->change();
            $table->string('fase_origen')->nullable()->change();
            $table->string('fase_destino')->nullable()->change();
        });

        Schema::table('transiciones', function (Blueprint $table) {
            // Foreign keys para estados
            $table->foreign('estado_origen')
                ->references('slug')->on('estados')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('estado_destino')
                ->references('slug')->on('estados')
                ->cascadeOnUpdate()->restrictOnDelete();

            // Foreign keys para fases
            $table->foreign('fase_origen')
                ->references('slug')->on('fase')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('fase_destino')
                ->references('slug')->on('fase')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transiciones', function (Blueprint $table) {
            $table->dropForeign(['estado_origen']);
            $table->dropForeign(['estado_destino']);
            $table->dropForeign(['fase_origen']);
            $table->dropForeign(['fase_destino']);
        });
    }
};
