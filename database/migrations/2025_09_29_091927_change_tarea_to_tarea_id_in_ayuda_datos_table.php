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
        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 1) Añadir nueva columna tarea_id
            $table->unsignedBigInteger('tarea_id')->nullable()->after('fase');
        });

        // 2) Poblar tarea_id desde la columna tarea (slug)
        DB::table('ayuda_datos')
            ->join('tareas', 'ayuda_datos.tarea', '=', 'tareas.slug')
            ->update([
                'ayuda_datos.tarea_id' => DB::raw('tareas.id'),
            ]);

        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 3) Eliminar foreign key de tarea
            $table->dropForeign(['tarea']);

            // 4) Eliminar columna tarea
            $table->dropColumn('tarea');

            // 5) Añadir foreign key para tarea_id
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
        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 1) Eliminar foreign key de tarea_id
            $table->dropForeign(['tarea_id']);

            // 2) Añadir columna tarea
            $table->string('tarea', 191)->nullable()->after('fase');
        });

        // 3) Poblar tarea desde tarea_id
        DB::table('ayuda_datos')
            ->join('tareas', 'ayuda_datos.tarea_id', '=', 'tareas.id')
            ->update([
                'ayuda_datos.tarea' => DB::raw('tareas.slug'),
            ]);

        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 4) Eliminar columna tarea_id
            $table->dropColumn('tarea_id');

            // 5) Añadir foreign key para tarea
            $table->foreign('tarea')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
};
