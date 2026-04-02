<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoAndComisionToContrataciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modificar la tabla `contrataciones`
        Schema::table('contrataciones', function (Blueprint $table) {
            // Cambiar o agregar el campo `estado`
            $table->enum('estado', ['procesando', 'tramitando', 'tramitada', 'concedida', 'rechazada', 'documentación', 'resolución provisional', 'subsanación', 'resolución definitiva', 'subsanación mediante reposición', 'lista de espera por insuficiencia de crédito', 'ayuda aprobada', 'ayuda recibida', 'desistida', 'renunciada', 'devolución', 'finalizada'])->default('procesando')->after('fecha_contratacion');
            $table->decimal('monto_comision', 10, 2)->default(0.00)->after('estado'); // Comisión (20%)
            $table->decimal('monto_total_ayuda', 10, 2)->default(0.00)->after('monto_comision'); // Monto total concedido
        });

        // Crear la tabla `pagos`
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contratacion_id')->constrained('contrataciones')->onDelete('cascade'); // Relación con contrataciones
            $table->decimal('monto', 10, 2); // Monto cobrado (comisión)
            $table->enum('estado', ['pendiente', 'exitoso', 'rechazado', 'error'])->default('pendiente'); // Estado del pago
            $table->text('respuesta_stripe')->nullable(); // Respuesta de Stripe
            $table->timestamp('fecha_pago')->nullable(); // Fecha de pago
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar los cambios si se hace rollback
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->dropColumn(['estado', 'monto_comision', 'monto_total_ayuda']);
        });

        // Eliminar la tabla `pagos`
        Schema::dropIfExists('pagos');
    }
}
