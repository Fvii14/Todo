<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAyudasTable extends Migration
{
    public function up()
    {
        Schema::create('user_ayudas', function (Blueprint $table) {
            $table->id(); // id autoincremental
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_ayuda');
            $table->boolean('benef')->default(false);
            $table->timestamps();

            // Relaciones tablas users y ayudas
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_ayuda')->references('id')->on('ayudas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_ayudas');
    }
}
