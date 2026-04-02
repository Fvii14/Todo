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
        Schema::table('transiciones', function (Blueprint $table) {
            // Eliminar el índice de la columna ayuda
            $table->dropIndex(['ayuda']);

            // Eliminar la columna ayuda
            $table->dropColumn('ayuda');

            // Agregar la nueva columna ayuda_id como foreign key
            $table->unsignedBigInteger('ayuda_id')->nullable();
            $table->foreign('ayuda_id')
                ->references('id')->on('ayudas')
                ->cascadeOnUpdate()->restrictOnDelete();

            // Agregar índice para mejorar consultas
            $table->index('ayuda_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transiciones', function (Blueprint $table) {
            // Eliminar foreign key y índice de ayuda_id
            $table->dropForeign(['ayuda_id']);
            $table->dropIndex(['ayuda_id']);

            // Eliminar la columna ayuda_id
            $table->dropColumn('ayuda_id');

            // Restaurar la columna ayuda original
            $table->string('ayuda', 255)->nullable()->collation('utf8mb4_general_ci');

            // Restaurar el índice de ayuda
            $table->index('ayuda');
        });
    }
};
