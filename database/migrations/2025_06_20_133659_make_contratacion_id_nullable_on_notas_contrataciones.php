<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeContratacionIdNullableOnNotasContrataciones extends Migration
{
    public function up()
    {
        Schema::table('notas_contrataciones', function (Blueprint $table) {
            // Primero hay que soltar la FK existente
            $table->dropForeign(['contratacion_id']);
            // Luego modificar la columna para que admita NULL
            $table->unsignedBigInteger('contratacion_id')->nullable()->change();
            // Y volver a añadir la FK
            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('notas_contrataciones', function (Blueprint $table) {
            $table->dropForeign(['contratacion_id']);
            $table->unsignedBigInteger('contratacion_id')->nullable(false)->change();
            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->onDelete('cascade');
        });
    }
}
