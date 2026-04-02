<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_subsanacion_contrataciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('contratacion_id');
            $table->unsignedBigInteger('motivo_id');

            $table->enum('estado_subsanacion', ['pendiente', 'completada']);

            $table->timestamps();

            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->cascadeOnDelete();

            $table->foreign('motivo_id')
                ->references('id')->on('motivos_subsanacion_ayuda')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivos_subsanacion_contrataciones');
    }
};
