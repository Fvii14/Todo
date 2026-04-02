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
        Schema::create('documento_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contratacion_id');
            $table->unsignedBigInteger('document_id');
            $table->boolean('visible')->default(true);
            $table->timestamps();

            $table->foreign('contratacion_id')->references('id')->on('contrataciones')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            $table->unique(['contratacion_id', 'document_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_configuraciones');
    }
};
