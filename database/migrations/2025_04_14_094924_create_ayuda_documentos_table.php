<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAyudaDocumentosTable extends Migration
{
    public function up()
    {
        Schema::create('ayuda_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ayuda_id');
            $table->unsignedBigInteger('documento_id');
            $table->boolean('es_obligatorio')->default(false);
            $table->timestamps();

            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('cascade');
            $table->foreign('documento_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ayuda_documentos');
    }
}
