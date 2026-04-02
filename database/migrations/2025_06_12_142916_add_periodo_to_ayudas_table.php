<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodoToAyudasTable extends Migration
{
    public function up()
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->date('fecha_inicio_periodo')->nullable()->after('nombre_ayuda');
            $table->date('fecha_fin_periodo')->nullable()->after('fecha_inicio_periodo');
        });
    }

    public function down()
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio_periodo', 'fecha_fin_periodo']);
        });
    }
}
