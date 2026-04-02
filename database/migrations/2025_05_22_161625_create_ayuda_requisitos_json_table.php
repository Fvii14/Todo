<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ayuda_requisitos_json', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ayuda_id');
            $table->text('descripcion')->nullable();
            $table->longText('json_regla');
            $table->timestamps();

            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ayuda_requisitos_json');
    }
};
