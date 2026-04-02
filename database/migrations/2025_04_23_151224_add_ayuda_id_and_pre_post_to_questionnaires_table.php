<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAyudaIdAndPrePostToQuestionnairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            // Añadir la columna 'ayuda_id' como clave foránea que hace referencia a la tabla 'ayudas'
            $table->unsignedBigInteger('ayuda_id')->nullable(); // Puede ser nullable si no es obligatorio
            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('set null'); // Relación con la tabla 'ayudas'

            // Añadir el campo booleano 'pre_post' para indicar si es pre-pago o post-pago
            $table->boolean('pre_post')->default(false); // Por defecto será false (post-pago)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            // Eliminar las columnas 'ayuda_id' y 'pre_post' si se revierte la migración
            $table->dropForeign(['ayuda_id']);
            $table->dropColumn(['ayuda_id', 'pre_post']);
        });
    }
}
