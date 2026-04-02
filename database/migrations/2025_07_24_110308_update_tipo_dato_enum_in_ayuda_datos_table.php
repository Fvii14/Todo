<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ayuda_datos MODIFY tipo_dato ENUM(
            'solicitante',
            'conviviente',
            'contrato',
            'hijo',
            'arrendador'
        ) NOT NULL");
    }

    public function down(): void
    {
        // Revertimos eliminando el valor 'arrendador'
        DB::statement("ALTER TABLE ayuda_datos MODIFY tipo_dato ENUM(
            'solicitante',
            'conviviente',
            'contrato',
            'hijo'
        ) NOT NULL");
    }
};
