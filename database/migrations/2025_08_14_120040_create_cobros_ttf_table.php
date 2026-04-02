<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobros_ttf', function (Blueprint $table) {
            $table->id();

            // Relación directa por trazabilidad
            $table->unsignedBigInteger('pago_admin_id');      // FK a pagos_administracion.id
            $table->unsignedBigInteger('contratacion_id');    // redundante pero práctico para filtros
            $table->unsignedBigInteger('factura_id')->nullable();

            // Importe de la comisión cobrada o a cobrar (puede coincidir con monto_comision o ser parcial)
            $table->decimal('cantidad_comision', 10, 2);

            // Estado del cobro
            $table->enum('estado', ['pendiente', 'cobrada'])->default('pendiente');

            $table->text('notas')->nullable();

            // (Opcional) fechas útiles
            $table->date('fecha_prevista_cobro')->nullable();
            $table->date('fecha_cobro')->nullable();

            $table->timestamps();

            // FKs
            $table->foreign('pago_admin_id')
                ->references('id')->on('pagos_administracion')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('contratacion_id')
                ->references('id')->on('contrataciones')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('factura_id')
                ->references('id')->on('facturas')
                ->onUpdate('cascade')->onDelete('set null');

            // Índices
            $table->index(['contratacion_id', 'estado']);
            $table->index(['pago_admin_id']);
            $table->index(['factura_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobros_ttf');
    }
};
