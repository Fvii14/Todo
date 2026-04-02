<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            // Añadimos el campo nullable
            $table->unsignedBigInteger('tramitador_id')->nullable()->after('user_id');

            // Clave foránea a users.id
            $table
                ->foreign('tramitador_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            // Eliminamos la FK primero
            $table->dropForeign(['tramitador_id']);

            // Y luego la columna
            $table->dropColumn('tramitador_id');
        });
    }
};
