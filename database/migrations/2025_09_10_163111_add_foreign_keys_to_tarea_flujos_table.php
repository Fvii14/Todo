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
        Schema::table('tarea_flujos', function (Blueprint $table) {
            // No crear foreign keys para tarea_origen y tarea_destino
            // porque serán manejadas por la migración posterior que las convierte a IDs

            $table->foreign('opcion_tarea_origen_id')
                ->references('id')->on('opciones_tareas')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('opcion_tarea_destino_id')
                ->references('id')->on('opciones_tareas')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarea_flujos', function (Blueprint $table) {
            // No eliminar foreign keys para tarea_origen y tarea_destino
            // porque no se crearon en el método up()

            $table->dropForeign(['opcion_tarea_origen_id']);
            $table->dropForeign(['opcion_tarea_destino_id']);
        });
    }
};
