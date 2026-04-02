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
        // Crear la tabla sin foreign keys primero con la misma collation que tareas
        Schema::create('tarea_flujos', function (Blueprint $table) {
            $table->id();

            $table->string('tarea_origen', 255)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('opcion_tarea_origen_id');

            $table->string('tarea_destino', 255)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('opcion_tarea_destino_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarea_flujos');
    }
};
