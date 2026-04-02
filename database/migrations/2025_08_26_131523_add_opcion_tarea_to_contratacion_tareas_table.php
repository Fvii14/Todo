<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('opcion_tarea')
                ->nullable()
                ->after('tarea');

            $table->foreign('opcion_tarea')
                ->references('id')->on('opciones_tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('opcion_tarea');
        });
    }

    public function down(): void
    {
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->dropForeign(['opcion_tarea']);
            $table->dropIndex('contratacion_tareas_opcion_tarea_index');
            $table->dropColumn('opcion_tarea');
        });
    }
};
