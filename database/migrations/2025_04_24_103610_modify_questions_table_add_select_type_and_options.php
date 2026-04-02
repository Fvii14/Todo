<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyQuestionsTableAddSelectTypeAndOptions extends Migration
{
    public function up()
    {
        // Cambiar el tipo del campo 'type' para incluir 'select'
        DB::statement("ALTER TABLE `questions` CHANGE `type` `type` ENUM('integer', 'boolean', 'string', 'date', 'select') NOT NULL");

        // Añadir el campo 'options' de tipo JSON para almacenar las opciones posibles de respuesta
        Schema::table('questions', function (Blueprint $table) {
            $table->json('options')->nullable()->after('type'); // Almacenará las opciones como un array JSON
        });
    }

    public function down()
    {
        // Si revertimos la migración, eliminamos el campo 'options' y volvemos a quitar 'select' del enum
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('options');
        });

        DB::statement("ALTER TABLE `questions` CHANGE `type` `type` ENUM('integer', 'boolean', 'string', 'date') NOT NULL");
    }
}
