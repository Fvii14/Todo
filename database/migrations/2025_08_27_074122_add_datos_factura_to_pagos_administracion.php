<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_administracion', function (Blueprint $table) {
            // Campos de factura (Holded + PDF en GCS)
            $table->string('factura_holded_id', 64)
                ->nullable()
                ->after('estado_cobro');

            $table->string('factura_numero', 64)
                ->nullable()
                ->after('factura_holded_id');

            $table->timestamp('factura_emitida_at')
                ->nullable()
                ->after('factura_numero');

            $table->string('factura_pdf_gcs_path', 255)
                ->nullable()
                ->after('factura_emitida_at');

            // Índice para búsquedas por id de Holded
            $table->index('factura_holded_id', 'idx_pagos_admin_factura');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_administracion', function (Blueprint $table) {
            // Quitar índice y columnas añadidas
            $table->dropIndex('idx_pagos_admin_factura');

            $table->dropColumn([
                'factura_holded_id',
                'factura_numero',
                'factura_emitida_at',
            ]);
        });
    }
};
