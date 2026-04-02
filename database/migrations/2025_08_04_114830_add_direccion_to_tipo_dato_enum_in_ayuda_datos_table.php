<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
            'arrendador',
            'direccion'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ayuda_datos MODIFY tipo_dato ENUM(
            'solicitante',
            'conviviente',
            'contrato',
            'hijo',
            'arrendador'
        ) NOT NULL");
    }
};
