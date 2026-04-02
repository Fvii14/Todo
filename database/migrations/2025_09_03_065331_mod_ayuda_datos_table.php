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
        Schema::table('ayuda_datos', function (Blueprint $table) {
            $table->string('tarea', 191)->nullable()->after('fase');

            $table->unsignedBigInteger('opcion_tarea_id')->nullable()->after('tarea');

            $table->foreign('tarea')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('opcion_tarea_id')
                ->references('id')
                ->on('opciones_tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ayuda_datos', function (Blueprint $table) {
            // Eliminar foreign keys primero
            $table->dropForeign(['tarea']);
            $table->dropForeign(['opcion_tarea_id']);

            // Eliminar columnas
            $table->dropColumn(['tarea', 'opcion_tarea_id']);
        });
    }
};
