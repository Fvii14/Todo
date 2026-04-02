<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipio', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('provincia_id'); // NUEVO campo FK
            $table->string('codigo_municipio', 3);
            $table->string('nombre_municipio');
            $table->timestamps();

            // Clave foránea hacia el ID de la provincia
            $table->foreign('provincia_id')
                ->references('id')
                ->on('provincia')
                ->onDelete('cascade');

            // Asegurar combinación única por ID provincia y código municipio
            $table->unique(['provincia_id', 'codigo_municipio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipio');
    }
};
