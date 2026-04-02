<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrendatarios', function (Blueprint $table) {
            $table->id();

            // Relación con users
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Campos
            $table->enum('tipo_persona', ['Fisica', 'Juridica']);
            $table->string('documento_identidad', 50);
            $table->string('nombre_completo_razon_social', 255);

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrendatarios');
    }
};
