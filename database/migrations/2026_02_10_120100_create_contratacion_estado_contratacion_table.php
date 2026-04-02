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
        Schema::create('contratacion_estado_contratacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contratacion_id')
                ->constrained('contrataciones')
                ->cascadeOnDelete();
            $table->foreignId('estado_contratacion_id')
                ->constrained('estados_contratacion')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contratacion_id', 'estado_contratacion_id'], 'contrato_estado_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratacion_estado_contratacion');
    }
};
