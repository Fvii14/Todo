<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAyudaSolicitadasTable extends Migration
{
    public function up()
    {
        Schema::create('ayudas_solicitadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ayuda_id')->constrained()->onDelete('cascade');
            $table->enum('estado', ['Aprobado', 'Pendiente de tramitar', 'Presentada', 'Rechazado'])->default('Pendiente de tramitar');
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ayuda_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ayudas_solicitadas');
    }
}
