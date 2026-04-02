<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyOptionsColumnInQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            // Modificar el tipo de la columna 'options' a 'json'
            $table->json('options')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            // Revertir el tipo de la columna a 'longtext' en caso de rollback
            $table->longText('options')->nullable()->change();
        });
    }
}
