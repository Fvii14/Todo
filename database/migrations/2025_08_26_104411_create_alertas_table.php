<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('ayuda_id')->nullable();
            $table->unsignedBigInteger('contratacion_id')->nullable();

            $table->enum('tipo_plazo', ['mensual', 'personalizado']);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('tipo_alerta', ['justificacion', 'subsanacion', 'apertura']);

            $table->timestamps();

            $table->foreign('ayuda_id')
                ->references('id')->on('ayudas')
                ->nullOnDelete();

            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->nullOnDelete();

            $table->index('tipo_alerta');
            $table->index('tipo_plazo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
