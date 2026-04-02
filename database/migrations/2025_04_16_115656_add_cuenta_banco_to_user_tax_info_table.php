<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCuentaBancoToUserTaxInfoTable extends Migration
{
    public function up()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->string('cuenta_banco')->nullable()->after('domicilio_fiscal'); // puedes cambiar la posición si quieres
        });
    }

    public function down()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->dropColumn('cuenta_banco');
        });
    }
}
