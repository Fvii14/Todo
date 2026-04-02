<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetallesAyudaTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalles_ayuda', function (Blueprint $table) {
            $table->id();
            $table->timestamp('create_time')->nullable()->comment('Create Time');
            $table->unsignedBigInteger('ayuda_id');
            $table->enum('Temporalidad', ['Temporal', 'Permanente'])->default('Temporal');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedBigInteger('id_organismo');
            $table->unsignedBigInteger('id_ccaa')->nullable();
            $table->float('cantidad_max');
            $table->float('cantidad_min');
            $table->integer('año')->nullable();
            $table->float('presupuesto')->default(0)->comment('Cantidad otorgada a la ayuda');
            $table->enum('tipo_persona_bene', ['Juridica', 'Fisica'])->default('Fisica')->comment('Tipo de persona');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('ayuda_id')->references('id')->on('ayudas')->onDelete('cascade');
            $table->foreign('id_organismo')->references('id')->on('organos')->onDelete('cascade');
            $table->foreign('id_ccaa')->references('id')->on('ccaa')->onDelete('set null');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_ayuda');
    }
}
