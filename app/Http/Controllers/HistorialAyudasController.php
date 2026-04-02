<?php

namespace App\Http\Controllers;

use App\Models\Contratacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HistorialAyudasController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Recupera las contrataciones asociadas al usuario autenticado
        $historialContrataciones = Contratacion::where('user_id', $user->id)
            ->with(['ayuda', 'producto'])
            ->get();

        // Mapea la información que quieres mostrar
        $historialAyudas = $historialContrataciones->map(function ($contratacion) {
            // Normalizar estado/fase desde OPx si vienen nulos (para compatibilidad con formato legacy)
            $this->normalizarEstadoFaseDesdeOPx($contratacion);

            // Mapear el sistema actual (estado + fase) al formato legacy que espera la vista
            $estado = $this->mapearEstadoALegacy($contratacion->estado, $contratacion->fase);

            return [
                'monto_comision' => $contratacion->monto_comision,
                'monto_total_ayuda' => $contratacion->monto_total_ayuda,
                'fecha_contratacion' => Carbon::parse($contratacion->fecha_contratacion)->toDateString(),
                'estado' => $estado,
                'producto' => optional($contratacion->producto)->product_name,
            ];
        });

        // Retorna la vista con los datos
        return view('user.historial-ayudas', compact('historialAyudas'));
    }

    /**
     * Si estado/fase están vacíos, derivarlos desde estados OPx.
     */
    private function normalizarEstadoFaseDesdeOPx(Contratacion $contratacion): void
    {
        if ($contratacion->estado !== null && $contratacion->fase !== null) {
            return;
        }

        $contratacion->loadMissing('estadosContratacion');
        $codigos = $contratacion->estadosContratacion->pluck('codigo')->all();

        if (empty($codigos)) {
            return;
        }

        if (in_array('OP1-Resolucion', $codigos, true)) {
            $contratacion->estado = $contratacion->estado ?? 'cierre';
            $contratacion->fase = $contratacion->fase ?? 'resolucion';
        } elseif (in_array('OP1-Tramitacion', $codigos, true)) {
            $contratacion->estado = $contratacion->estado ?? 'tramitacion';
            $contratacion->fase = $contratacion->fase ?? 'en_seguimiento';
        } elseif (in_array('OP1-Documentacion', $codigos, true)) {
            $contratacion->estado = $contratacion->estado ?? 'documentacion';
            $contratacion->fase = $contratacion->fase ?? 'documentacion';
        }
    }

    /**
     * Mapea el nuevo sistema de estados (estado + fase) al formato legacy que espera la vista
     */
    private function mapearEstadoALegacy(?string $estado, ?string $fase): string
    {
        // Cierre: resolucion = concedida, rechazada = rechazada
        if ($estado === 'cierre') {
            if ($fase === 'resolucion') {
                return 'concedida';
            }
            if ($fase === 'rechazada') {
                return 'rechazada';
            }

            // Cierre sin fase específica → procesando
            return 'procesando';
        }

        // Tramitación: presentada = tramitada, otras fases = procesando
        if ($estado === 'tramitacion') {
            if ($fase === 'presentada') {
                return 'tramitada';
            }

            // Otras fases de tramitación (apertura, en_seguimiento) → procesando
            return 'procesando';
        }

        // Documentación → procesando
        if ($estado === 'documentacion') {
            return 'procesando';
        }

        // Estado desconocido o null → procesando (mejor que mostrar error)
        return 'procesando';
    }
}
