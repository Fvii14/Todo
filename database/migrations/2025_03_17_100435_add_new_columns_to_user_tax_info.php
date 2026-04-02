<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->string('domicilio')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('municipio')->nullable();
            $table->string('provincia')->nullable();
            $table->string('entidad_colectiva')->nullable();
            $table->string('entidad_singular')->nullable();
            $table->string('nucleo_diseminado')->nullable();
            $table->date('fecha_variacion')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->dropColumn([
                'domicilio',
                'codigo_postal',
                'municipio',
                'provincia',
                'entidad_colectiva',
                'entidad_singular',
                'nucleo_diseminado',
                'fecha_variacion',
            ]);
        });
    }
};
