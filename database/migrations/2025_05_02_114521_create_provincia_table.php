<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provincia', function (Blueprint $table) {
            $table->id(); // ID autoincremental como clave primaria
            $table->string('codigo_provincia', 2)->unique(); // Código INE, sigue siendo clave externa en otras tablas
            $table->unsignedBigInteger('id_ccaa');
            $table->string('nombre_provincia');
            $table->timestamps();

            $table->foreign('id_ccaa')
                ->references('id')
                ->on('ccaa')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provincia');
    }
};
