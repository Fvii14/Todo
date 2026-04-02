<?php

namespace App\Observers;

use App\Models\Contratacion;
use App\Models\Conviviente;
use App\Services\EstadoContratacionService;
use Illuminate\Support\Facades\Log;

class ConvivienteObserver
{
    public function updated(Conviviente $conviviente)
    {
        if ($conviviente->completo) {

            $contratacion = Contratacion::where('user_id', $conviviente->user_id)
                ->where('ayuda_id', $conviviente->ayuda_id)
                ->first();

            // Si está en documentación (OPx) y está completa → añadir OP1-Tramitacion
            if ($contratacion && $contratacion->estadosContratacion()->where('codigo', 'OP1-Documentacion')->exists()) {
                if ($contratacion->isCompleta()) {
                    app(EstadoContratacionService::class)->syncEstadosByCodigos(
                        $contratacion,
                        ['OP1-Tramitacion'],
                        false
                    );
                    Log::info("✅ Contratación {$contratacion->id}: añadido OP1-Tramitacion (convivientes completos)");
                }
            }
        }
    }
}
