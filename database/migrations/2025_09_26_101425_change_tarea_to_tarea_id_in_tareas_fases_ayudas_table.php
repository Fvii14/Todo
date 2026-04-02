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
        // Verificar si la columna tarea_id ya existe
        if (! Schema::hasColumn('tareas_fases_ayudas', 'tarea_id')) {
            // Agregar la nueva columna tarea_id
            Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
                $table->unsignedBigInteger('tarea_id')->nullable()->after('fase');
            });
        }

        // Migrar los datos existentes de slug a tarea_id si es necesario
        $tareasFasesAyudas = DB::table('tareas_fases_ayudas')->whereNull('tarea_id')->get();

        foreach ($tareasFasesAyudas as $tareaFaseAyuda) {
            $tarea = DB::table('tareas')->where('slug', $tareaFaseAyuda->tarea)->first();
            if ($tarea) {
                DB::table('tareas_fases_ayudas')
                    ->where('id', $tareaFaseAyuda->id)
                    ->update(['tarea_id' => $tarea->id]);
            }
        }

        // Hacer tarea_id no nullable
        Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
            $table->unsignedBigInteger('tarea_id')->nullable(false)->change();
        });

        // Agregar nuevo índice único con tarea_id primero si no existe
        try {
            Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
                $table->unique(['ayuda_id', 'fase', 'tarea_id'], 'tareas_fases_ayudas_ayuda_id_fase_tarea_id_unique');
            });
        } catch (\Exception $e) {
            // El índice ya existe, continuar
        }

        // Eliminar la foreign key de la columna tarea si existe
        try {
            Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
                $table->dropForeign(['tarea']);
            });
        } catch (\Exception $e) {
            // La foreign key no existe, continuar
        }

        // Eliminar el índice único que incluye la columna tarea si existe
        try {
            Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
                $table->dropUnique('tareas_fases_ayudas_ayuda_id_fase_tarea_unique');
            });
        } catch (\Exception $e) {
            // El índice no existe, continuar
        }

        // Eliminar el índice de la columna tarea si existe
        try {
            Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
                $table->dropIndex('tareas_fases_ayudas_tarea_index');
            });
        } catch (\Exception $e) {
            // El índice no existe, continuar
        }

        // Eliminar la columna tarea (slug) si existe
        if (Schema::hasColumn('tareas_fases_ayudas', 'tarea')) {
            Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
                $table->dropColumn('tarea');
            });
        }

        // Agregar la nueva foreign key si no existe
        try {
            Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
                $table->foreign('tarea_id')
                    ->references('id')
                    ->on('tareas')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        } catch (\Exception $e) {
            // La foreign key ya existe, continuar
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key nueva
        Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
            $table->dropForeign(['tarea_id']);
        });

        // Agregar la columna tarea (slug) de vuelta
        Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
            $table->string('tarea')->after('fase');
        });

        // Migrar los datos de tarea_id a tarea (slug)
        $tareasFasesAyudas = DB::table('tareas_fases_ayudas')->get();

        foreach ($tareasFasesAyudas as $tareaFaseAyuda) {
            $tarea = DB::table('tareas')->where('id', $tareaFaseAyuda->tarea_id)->first();
            if ($tarea) {
                DB::table('tareas_fases_ayudas')
                    ->where('id', $tareaFaseAyuda->id)
                    ->update(['tarea' => $tarea->slug]);
            }
        }

        // Agregar la foreign key antigua
        Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
            $table->foreign('tarea')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // Eliminar la columna tarea_id
        Schema::table('tareas_fases_ayudas', function (Blueprint $table) {
            $table->dropColumn('tarea_id');
        });
    }
};
