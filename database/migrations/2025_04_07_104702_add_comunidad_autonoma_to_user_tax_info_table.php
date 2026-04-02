<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComunidadAutonomaToUserTaxInfoTable extends Migration
{
    public function up()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->text('comunidad_autonoma')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->dropColumn('comunidad_autonoma');
        });
    }
}
