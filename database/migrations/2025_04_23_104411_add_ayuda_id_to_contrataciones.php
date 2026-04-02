<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAyudaIdToContrataciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            // Agregar columna ayuda_id
            $table->foreignId('ayuda_id')->constrained('ayudas')->onDelete('cascade')->after('user_id');  // Relación con la tabla 'ayudas'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            // Eliminar la columna ayuda_id si revertimos la migración
            $table->dropColumn('ayuda_id');
        });
    }
}
