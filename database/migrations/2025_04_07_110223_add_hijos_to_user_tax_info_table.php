<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHijosToUserTaxInfoTable extends Migration
{
    public function up()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->json('hijos')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->dropColumn('hijos');
        });
    }
}
