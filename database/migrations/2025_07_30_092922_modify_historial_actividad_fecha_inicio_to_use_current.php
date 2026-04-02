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
        Schema::table('historial_actividad', function (Blueprint $table) {
            // Primero eliminamos el campo fecha_inicio existente
            $table->dropColumn('fecha_inicio');
        });

        Schema::table('historial_actividad', function (Blueprint $table) {
            // Luego añadimos el nuevo campo fecha_inicio como timestamp con useCurrent()
            $table->timestamp('fecha_inicio')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_actividad', function (Blueprint $table) {
            // Eliminamos el campo timestamp
            $table->dropColumn('fecha_inicio');
        });

        Schema::table('historial_actividad', function (Blueprint $table) {
            // Restauramos el campo dateTime original
            $table->dateTime('fecha_inicio');
        });
    }
};
