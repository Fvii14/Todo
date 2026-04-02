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
        Schema::table('contratacion_documentos_tramitacion', function (Blueprint $table) {
            // Eliminar el índice existente que causa el problema de unicidad
            $table->dropIndex(['contratacion_id', 'slug']);

            // Crear un nuevo índice que permita duplicados pero mantenga el rendimiento
            $table->index(['contratacion_id', 'slug', 'nombre_personalizado'], 'idx_contratacion_slug_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratacion_documentos_tramitacion', function (Blueprint $table) {
            // Revertir los cambios: eliminar el nuevo índice y restaurar el original
            $table->dropIndex('idx_contratacion_slug_nombre');
            $table->index(['contratacion_id', 'slug']);
        });
    }
};
