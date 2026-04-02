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
        // Verificar si las columnas ya existen
        if (! Schema::hasColumn('tarea_flujos', 'tarea_origen_id')) {
            // Agregar las nuevas columnas tarea_origen_id y tarea_destino_id
            Schema::table('tarea_flujos', function (Blueprint $table) {
                $table->unsignedBigInteger('tarea_origen_id')->nullable()->after('id');
                $table->unsignedBigInteger('tarea_destino_id')->nullable()->after('tarea_origen_id');
            });
        }

        // Migrar los datos existentes de slug a tarea_id si es necesario
        $tareaFlujos = DB::table('tarea_flujos')->whereNull('tarea_origen_id')->get();

        foreach ($tareaFlujos as $tareaFlujo) {
            // Migrar tarea_origen
            if ($tareaFlujo->tarea_origen) {
                $tareaOrigen = DB::table('tareas')->where('slug', $tareaFlujo->tarea_origen)->first();
                if ($tareaOrigen) {
                    DB::table('tarea_flujos')
                        ->where('id', $tareaFlujo->id)
                        ->update(['tarea_origen_id' => $tareaOrigen->id]);
                }
            }

            // Migrar tarea_destino
            if ($tareaFlujo->tarea_destino) {
                $tareaDestino = DB::table('tareas')->where('slug', $tareaFlujo->tarea_destino)->first();
                if ($tareaDestino) {
                    DB::table('tarea_flujos')
                        ->where('id', $tareaFlujo->id)
                        ->update(['tarea_destino_id' => $tareaDestino->id]);
                }
            }
        }

        // Hacer las columnas no nullable
        Schema::table('tarea_flujos', function (Blueprint $table) {
            $table->unsignedBigInteger('tarea_origen_id')->nullable(false)->change();
            $table->unsignedBigInteger('tarea_destino_id')->nullable(false)->change();
        });

        // Eliminar las foreign keys antiguas si existen
        try {
            Schema::table('tarea_flujos', function (Blueprint $table) {
                $table->dropForeign(['tarea_origen']);
            });
        } catch (\Exception $e) {
            // La foreign key no existe, continuar
        }

        try {
            Schema::table('tarea_flujos', function (Blueprint $table) {
                $table->dropForeign(['tarea_destino']);
            });
        } catch (\Exception $e) {
            // La foreign key no existe, continuar
        }

        // Eliminar las columnas tarea_origen y tarea_destino (slug) si existen
        if (Schema::hasColumn('tarea_flujos', 'tarea_origen')) {
            Schema::table('tarea_flujos', function (Blueprint $table) {
                $table->dropColumn(['tarea_origen', 'tarea_destino']);
            });
        }

        // Agregar las nuevas foreign keys si no existen
        try {
            Schema::table('tarea_flujos', function (Blueprint $table) {
                $table->foreign('tarea_origen_id')
                    ->references('id')
                    ->on('tareas')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        } catch (\Exception $e) {
            // La foreign key ya existe, continuar
        }

        try {
            Schema::table('tarea_flujos', function (Blueprint $table) {
                $table->foreign('tarea_destino_id')
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
        // Eliminar las foreign keys nuevas
        Schema::table('tarea_flujos', function (Blueprint $table) {
            $table->dropForeign(['tarea_origen_id']);
            $table->dropForeign(['tarea_destino_id']);
        });

        // Agregar las columnas tarea_origen y tarea_destino (slug) de vuelta
        Schema::table('tarea_flujos', function (Blueprint $table) {
            $table->string('tarea_origen')->after('id');
            $table->string('tarea_destino')->after('tarea_origen');
        });

        // Migrar los datos de tarea_id a tarea (slug)
        $tareaFlujos = DB::table('tarea_flujos')->get();

        foreach ($tareaFlujos as $tareaFlujo) {
            // Migrar tarea_origen_id
            if ($tareaFlujo->tarea_origen_id) {
                $tareaOrigen = DB::table('tareas')->where('id', $tareaFlujo->tarea_origen_id)->first();
                if ($tareaOrigen) {
                    DB::table('tarea_flujos')
                        ->where('id', $tareaFlujo->id)
                        ->update(['tarea_origen' => $tareaOrigen->slug]);
                }
            }

            // Migrar tarea_destino_id
            if ($tareaFlujo->tarea_destino_id) {
                $tareaDestino = DB::table('tareas')->where('id', $tareaFlujo->tarea_destino_id)->first();
                if ($tareaDestino) {
                    DB::table('tarea_flujos')
                        ->where('id', $tareaFlujo->id)
                        ->update(['tarea_destino' => $tareaDestino->slug]);
                }
            }
        }

        // Agregar las foreign keys antiguas
        Schema::table('tarea_flujos', function (Blueprint $table) {
            $table->foreign('tarea_origen')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('tarea_destino')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // Eliminar las columnas tarea_origen_id y tarea_destino_id
        Schema::table('tarea_flujos', function (Blueprint $table) {
            $table->dropColumn(['tarea_origen_id', 'tarea_destino_id']);
        });
    }
};
