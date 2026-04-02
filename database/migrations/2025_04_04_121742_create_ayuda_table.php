<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAyudaTable extends Migration
{
    public function up()
    {
        Schema::create('ayudas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_ayuda');
            $table->string('sector');
            $table->timestamp('create_time')->nullable();
            $table->unsignedBigInteger('questionnaire_id')->nullable();
            $table->float('presupuesto')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->unsignedBigInteger('organo_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Claves foráneas
            $table->foreign('questionnaire_id')->references('id')->on('questionnaires')->onDelete('set null');
            $table->foreign('organo_id')->references('id')->on('organos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ayudas');
    }
}
