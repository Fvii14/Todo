<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas_subfases_ayudas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ayuda_id')
                ->constrained('ayudas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('subfase');

            $table->string('tarea');

            $table->timestamps();

            $table->index('subfase');
            $table->index('tarea');
            $table->unique(['ayuda_id', 'subfase', 'tarea'], 'uniq_ayuda_subfase_tarea');

            $table->foreign('subfase')
                ->references('slug')
                ->on('subfase')
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
        Schema::dropIfExists('tareas_subfases_ayudas');
    }
};
