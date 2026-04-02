<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_actividad', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Relación con la tabla contrataciones
            $table->unsignedBigInteger('contratacion_id');
            $table->dateTime('fecha_inicio');
            $table->string('actividad');
            $table->text('observaciones')->nullable();

            // Foreign key
            $table->foreign('contratacion_id')
                ->references('id')
                ->on('contrataciones')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('actividad_historial');
    }
};
