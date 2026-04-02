<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_administracion', function (Blueprint $table) {
            // Estado del cobro de la comisión asociada a este pago:
            // - no_aplica: el pago no lleva comisión (comisión nula o 0)
            // - pendiente: hay comisión > 0, aún no cobrada
            // - cobrada  : comisión cobrada (se habrá registrado en cobros_ttf)
            $table->enum('estado_cobro', ['no_aplica', 'pendiente', 'cobrada'])
                ->default('no_aplica')
                ->after('comision');

            $table->index(['contratacion_id', 'estado_cobro'], 'idx_contratacion_estado_cobro');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_administracion', function (Blueprint $table) {
            $table->dropIndex('idx_contratacion_estado_cobro');
            $table->dropColumn('estado_cobro');
        });
    }
};
