<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contratacion_pagos', function (Blueprint $table) {
            $table->id();

            // Relación con contrataciones
            $table->unsignedBigInteger('contratacion_id');
            $table->foreign('contratacion_id')
                ->references('id')
                ->on('contrataciones')
                ->onDelete('cascade');

            // Relación con payments usando payment_id (string)
            $table->string('payment_id');
            $table->foreign('payment_id')
                ->references('payment_id')
                ->on('payments')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contratacion_pagos');
    }
};
