<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicaciones_operativa', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tramitador_id');
            $table->enum('tipo_comunicacion', ['WhatsApp', 'Llamada']);
            $table->dateTime('fecha_hora');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('tramitador_id');
            $table->foreign('tramitador_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comunicaciones_operativa', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['tramitador_id']);
        });

        Schema::dropIfExists('comunicaciones_operativa');
    }
};
