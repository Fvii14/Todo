<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratacion_tareas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contratacion_id')
                ->constrained('contrataciones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('tarea');

            $table->enum('estado_tarea', ['pendiente', 'completada'])
                ->default('pendiente');

            $table->timestamps();

            $table->index('tarea');
            $table->index('estado_tarea');

            $table->foreign('tarea')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratacion_tareas');
    }
};
