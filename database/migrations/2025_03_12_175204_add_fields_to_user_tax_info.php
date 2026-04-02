<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->string('telefono')->nullable();
            $table->boolean('sin_deudas_ss')->default(false);
            $table->boolean('esta_trabajando')->default(false);
        });
    }

    public function down()
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'sin_deudas_ss', 'esta_trabajando']);
        });
    }
};
