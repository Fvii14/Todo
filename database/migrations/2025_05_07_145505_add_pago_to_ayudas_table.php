<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPagoToAyudasTable extends Migration
{
    public function up()
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->boolean('pago')->default(0)->after('cuantia_usuario');
        });
    }

    public function down()
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->dropColumn('pago');
        });
    }
}
