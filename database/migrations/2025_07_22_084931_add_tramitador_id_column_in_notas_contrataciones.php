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
        Schema::table('notas_contrataciones', function (Blueprint $table) {
            // Añade la columna tramitador_id como unsignedBigInteger, nullable
            $table->unsignedBigInteger('tramitador_id')->nullable()->after('user_id');

            // Si quieres clave foránea a users:
            $table->foreign('tramitador_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_contratacion', function (Blueprint $table) {
            // Eliminar la clave foránea primero
            $table->dropForeign(['tramitador_id']);

            // Luego eliminar la columna
            $table->dropColumn('tramitador_id');
        });
    }
};
