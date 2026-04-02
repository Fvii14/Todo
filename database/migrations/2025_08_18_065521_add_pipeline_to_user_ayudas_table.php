<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPipelineToUserAyudasTable extends Migration
{
    public function up()
    {
        Schema::table('user_ayudas', function (Blueprint $table) {
            $table->enum('pipeline', [
                'Captado',
                'Test hecho',
                'No cualificado',
                'Cualificado',
                'Cuestionario completado',
                'Beneficiario',
                'No beneficiario',
                'Contrata',
                'No contrata',
            ])->nullable()->after('estado_comercial');
        });
    }

    public function down()
    {
        Schema::table('user_ayudas', function (Blueprint $table) {
            $table->dropColumn('pipeline');
        });
    }
}
