<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, agregar la nueva columna tarea_id
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('tarea_id')->nullable()->after('descripcion');
        });

        // Migrar los datos existentes de slug a tarea_id
        $opcionesTareas = DB::table('opciones_tareas')->get();

        foreach ($opcionesTareas as $opcion) {
            $tarea = DB::table('tareas')->where('slug', $opcion->tarea)->first();
            if ($tarea) {
                DB::table('opciones_tareas')
                    ->where('id', $opcion->id)
                    ->update(['tarea_id' => $tarea->id]);
            }
        }

        // Hacer tarea_id no nullable
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('tarea_id')->nullable(false)->change();
        });

        // Eliminar la foreign key antigua
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->dropForeign(['tarea']);
        });

        // Eliminar la columna tarea (slug)
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->dropColumn('tarea');
        });

        // Agregar la nueva foreign key
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->foreign('tarea_id')
                ->references('id')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key nueva
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->dropForeign(['tarea_id']);
        });

        // Agregar la columna tarea (slug) de vuelta
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->string('tarea', 191)->after('descripcion');
        });

        // Migrar los datos de tarea_id a tarea (slug)
        $opcionesTareas = DB::table('opciones_tareas')->get();

        foreach ($opcionesTareas as $opcion) {
            $tarea = DB::table('tareas')->where('id', $opcion->tarea_id)->first();
            if ($tarea) {
                DB::table('opciones_tareas')
                    ->where('id', $opcion->id)
                    ->update(['tarea' => $tarea->slug]);
            }
        }

        // Agregar la foreign key antigua
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->foreign('tarea')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // Eliminar la columna tarea_id
        Schema::table('opciones_tareas', function (Blueprint $table) {
            $table->dropColumn('tarea_id');
        });
    }
};
