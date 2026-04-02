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
        Schema::create('ayuda_documentos_convivientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ayuda_id');
            $table->unsignedBigInteger('documento_id');
            $table->boolean('es_obligatorio')->default(false);
            $table->timestamps();

            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('cascade');
            $table->foreign('documento_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayuda_documentos_convivientes');
    }
};
