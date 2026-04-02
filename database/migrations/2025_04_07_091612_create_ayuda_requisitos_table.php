<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ayuda_requisitos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ayuda_id'); // Asegúrate de que sea unsignedBigInteger
            $table->unsignedBigInteger('question_id'); // Asegúrate de que sea unsignedBigInteger
            $table->string('respuesta_expected'); // Respuesta esperada
            $table->timestamps();

            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ayuda_requisitos');
    }
};
