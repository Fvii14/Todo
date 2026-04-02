<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_tax_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nif')->unique();
            $table->string('full_name');
            $table->string('domicilio_fiscal');
            $table->date('fecha_nacimiento');
            $table->string('estado_civil');
            $table->string('sexo');
            $table->bigInteger('base_imponible_general');
            $table->bigInteger('base_imponible_ahorro');
            $table->string('certificado_irpf')->nullable();
            $table->string('corriente_pago')->nullable();
            $table->string('sin_deudas');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_tax_info');
    }
};
