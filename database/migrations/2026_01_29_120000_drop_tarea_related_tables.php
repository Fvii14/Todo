<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina las tablas relacionadas con Tarea, TareaFlujo, OpcionTarea, etc.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Orden: primero tablas que referencian a otras de este grupo
        Schema::dropIfExists('tarea_flujos');
        Schema::dropIfExists('contratacion_tareas');
        Schema::dropIfExists('tareas_fases_ayudas');
        Schema::dropIfExists('tareas_subfases_ayudas');
        Schema::dropIfExists('opciones_tareas');
        Schema::dropIfExists('tarea_ayuda'); // TareaAyuda, por si existe
        Schema::dropIfExists('tareas');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     * No se recrean las tablas; ejecutar de nuevo las migraciones originales si se necesita.
     */
    public function down(): void
    {
        // Las tablas se eliminaron; el rollback no las recrea
    }
};
