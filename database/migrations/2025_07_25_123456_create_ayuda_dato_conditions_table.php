<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ayuda_dato_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ayuda_dato_id');
            $table->string('question_slug');
            $table->string('operador')->default('==');
            $table->string('valor'); // Puede ser string, número, o JSON para múltiple
            $table->timestamps();

            $table->foreign('ayuda_dato_id')->references('id')->on('ayuda_datos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ayuda_dato_conditions');
    }
};
