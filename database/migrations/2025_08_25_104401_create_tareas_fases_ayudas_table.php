<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas_fases_ayudas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ayuda_id')
                ->constrained('ayudas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('fase');

            $table->string('tarea');

            $table->timestamps();

            $table->index('fase');
            $table->index('tarea');
            $table->unique(['ayuda_id', 'fase', 'tarea']);

            $table->foreign('fase')
                ->references('slug')
                ->on('fase')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('tarea')
                ->references('slug')
                ->on('tareas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas_fases_ayudas');
    }
};
