<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocFaseToContratacionesTable extends Migration
{
    public function up()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->enum('doc_fase', ['Solicitud', 'Cotejo', 'Validación'])
                ->default('Solicitud')
                ->after('estado');
        });
    }

    public function down()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->dropColumn('doc_fase');
        });
    }
}
