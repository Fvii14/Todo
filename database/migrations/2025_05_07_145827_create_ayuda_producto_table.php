<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ayuda_producto', function (Blueprint $table) {
            $table->id(); // id autoincremental
            $table->unsignedBigInteger('ayuda_id');
            $table->unsignedBigInteger('product_id');

            // Claves foráneas
            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ayuda_producto');
    }
};
