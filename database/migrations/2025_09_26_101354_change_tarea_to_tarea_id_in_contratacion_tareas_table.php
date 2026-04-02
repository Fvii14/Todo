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
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('tarea_id')->nullable()->after('contratacion_id');
        });

        // Migrar los datos existentes de slug a tarea_id
        $contratacionTareas = DB::table('contratacion_tareas')->get();

        foreach ($contratacionTareas as $contratacionTarea) {
            $tarea = DB::table('tareas')->where('slug', $contratacionTarea->tarea)->first();
            if ($tarea) {
                DB::table('contratacion_tareas')
                    ->where('id', $contratacionTarea->id)
                    ->update(['tarea_id' => $tarea->id]);
            }
        }

        // Hacer tarea_id no nullable
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->unsignedBigInteger('tarea_id')->nullable(false)->change();
        });

        // Eliminar la foreign key antigua
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->dropForeign(['tarea']);
        });

        // Eliminar la columna tarea (slug)
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->dropColumn('tarea');
        });

        // Agregar la nueva foreign key
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->foreign('tarea_id')
                ->references('id')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key nueva
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->dropForeign(['tarea_id']);
        });

        // Agregar la columna tarea (slug) de vuelta
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->string('tarea')->after('contratacion_id');
        });

        // Migrar los datos de tarea_id a tarea (slug)
        $contratacionTareas = DB::table('contratacion_tareas')->get();

        foreach ($contratacionTareas as $contratacionTarea) {
            $tarea = DB::table('tareas')->where('id', $contratacionTarea->tarea_id)->first();
            if ($tarea) {
                DB::table('contratacion_tareas')
                    ->where('id', $contratacionTarea->id)
                    ->update(['tarea' => $tarea->slug]);
            }
        }

        // Agregar la foreign key antigua
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->foreign('tarea')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // Eliminar la columna tarea_id
        Schema::table('contratacion_tareas', function (Blueprint $table) {
            $table->dropColumn('tarea_id');
        });
    }
};
