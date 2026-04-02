<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contratacion_documentos_tramitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contratacion_id')->constrained('contrataciones')->onDelete('cascade');
            $table->string('slug');
            $table->string('nombre_personalizado')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index(['contratacion_id', 'slug']);
            $table->index(['contratacion_id', 'orden']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('contratacion_documentos_tramitacion');
    }
};
