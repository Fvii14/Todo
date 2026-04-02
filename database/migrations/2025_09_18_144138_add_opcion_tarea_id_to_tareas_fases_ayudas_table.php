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
        Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
            $table->unsignedBigInteger('opcion_tarea_id')
                ->nullable()
                ->after('tarea');
            $table->foreign('opcion_tarea_id')
                ->references('id')
                ->on('opciones_tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('opcion_tarea_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
            $table->dropForeign(['opcion_tarea_id']);
            $table->dropIndex('tareas_fases_ayudas_opcion_tarea_id_index');
            $table->dropColumn('opcion_tarea_id');
        });
    }
};
