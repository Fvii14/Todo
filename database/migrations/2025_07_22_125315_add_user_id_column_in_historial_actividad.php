<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_actividad', function (Blueprint $table) {
            // Añade la columna user_id como unsignedBigInteger, nullable
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // Si quieres clave foránea a users:
            $table->foreign('user_id')
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
        Schema::table('historial_actividad', function (Blueprint $table) {
            // Eliminar la clave foránea primero
            $table->dropForeign(['user_id']);

            // Luego eliminar la columna
            $table->dropColumn('user_id');
        });
    }
};
