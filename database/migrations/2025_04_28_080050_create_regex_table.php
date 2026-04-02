<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regex', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la validación (por ejemplo, "validar teléfono")
            $table->text('pattern'); // Expresión regular que valida la respuesta
            // añadir campo de mensaje de error para usarlo cuando la validación falle
            $table->string('error_message')->nullable(); // Mensaje de error que se mostrará si la validación falla
            $table->timestamps(); // Tiempos de creación y actualización
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regex');
    }
}
