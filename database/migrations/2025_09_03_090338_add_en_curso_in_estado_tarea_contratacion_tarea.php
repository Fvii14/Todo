<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::statement("
            ALTER TABLE contratacion_tareas
            MODIFY estado_tarea ENUM('pendiente','en_curso','completada')
            NOT NULL DEFAULT 'pendiente'
        ");
    }

    public function down(): void
    {

        DB::table('contratacion_tareas')
            ->where('estado_tarea', 'en_curso')
            ->update(['estado_tarea' => 'pendiente']);

        DB::statement("
            ALTER TABLE contratacion_tareas
            MODIFY estado_tarea ENUM('pendiente','completada')
            NOT NULL DEFAULT 'pendiente'
        ");
    }
};
