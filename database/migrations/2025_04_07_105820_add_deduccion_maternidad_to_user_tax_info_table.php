<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeduccionMaternidadToUserTaxInfoTable extends Migration
{
    public function up()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->boolean('deduccion_maternidad')->default(false);
        });
    }

    public function down()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->dropColumn('deduccion_maternidad');
        });
    }
}
