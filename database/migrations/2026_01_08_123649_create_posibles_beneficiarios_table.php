<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posibles_beneficiarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ayuda_id');
            $table->unsignedBigInteger('user_id');
            $table->string('nombre_completo')->nullable();
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->string('ccaa')->nullable();
            $table->timestamps();

            // Índices para consultas rápidas
            $table->index('ayuda_id');
            $table->index('user_id');
            $table->index(['ayuda_id', 'user_id']);

            // Foreign keys
            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posibles_beneficiarios');
    }
};
