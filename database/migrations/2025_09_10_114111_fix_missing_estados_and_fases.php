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
        // Caso especial: rechazada debe tener estado "cierre" y fase "rechazada"
        // Manejar este caso ANTES del mapeo general para poder identificar los registros
        DB::table('contrataciones')
            ->where('estado', 'rechazada')
            ->update([
                'estado' => 'cierre',
                'fase' => 'rechazada',
            ]);

        // Mapear otros estados antiguos a nuevos
        $mapeoEstados = [
            'finalizada' => 'cierre',      // finalizada → cierre
            'resolución' => 'cierre',      // resolución → cierre
            'tramitacion' => 'tramitacion', // ya está correcto
            'cierre' => 'cierre',          // ya está correcto
        ];

        foreach ($mapeoEstados as $estadoViejo => $estadoNuevo) {
            DB::table('contrataciones')
                ->where('estado', $estadoViejo)
                ->update(['estado' => $estadoNuevo]);
        }

        // Mapear fases antiguas a nuevas
        $mapeoFases = [
            'seguimiento' => null,         // seguimiento → NULL (no hay fase específica)
            'solicitud' => null,           // solicitud → NULL (no hay fase específica)
        ];

        foreach ($mapeoFases as $faseVieja => $faseNueva) {
            DB::table('contrataciones')
                ->where('fase', $faseVieja)
                ->update(['fase' => $faseNueva]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir mapeo de estados (no es posible revertir completamente)
        // ya que perdemos la información original
        Log::info('No es posible revertir completamente el mapeo de estados antiguos');
    }
};
