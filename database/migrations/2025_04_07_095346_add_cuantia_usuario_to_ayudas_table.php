<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->integer('cuantia_usuario')->nullable(); // Número entero
        });
    }

    public function down()
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->dropColumn('cuantia_usuario');
        });
    }
};
