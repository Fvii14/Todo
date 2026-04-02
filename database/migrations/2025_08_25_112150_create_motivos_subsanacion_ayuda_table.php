<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_subsanacion_ayuda', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('index')->nullable();

            $table->text('descripcion');

            $table->foreignId('ayuda_id')
                ->constrained('ayudas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('motivo', ['Padrón', 'Contrato', 'Recibos']);

            $table->timestamps();

            $table->index(['ayuda_id', 'motivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivos_subsanacion_ayuda');
    }
};
