<?php

namespace App\Services;

use App\Models\Contratacion;
use App\Models\HistorialActividad;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContratacionEstadoService
{
    protected EstadoService $estadoService;

    protected EstadoContratacionService $estadoContratacionService;

    public function __construct(
        EstadoService $estadoService,
        EstadoContratacionService $estadoContratacionService
    ) {
        $this->estadoService = $estadoService;
        $this->estadoContratacionService = $estadoContratacionService;
    }

    /**
     * Cambiar el estado de una contratación (sistema legacy estado/fase)
     *
     * @return array Datos del cambio realizado
     *
     * @throws \Exception
     */
    public function cambiarEstado(
        Contratacion $contratacion,
        string $nuevoEstadoSlug
    ): array {
        try {
            if (! $this->estadoService->isValidEstadoSlug($nuevoEstadoSlug)) {
                throw new \InvalidArgumentException("El estado '{$nuevoEstadoSlug}' no es válido");
            }

            $estadoAnterior = $contratacion->estado;
            $faseAnterior = $contratacion->fase;

            $contratacion->estado = $nuevoEstadoSlug;
            $contratacion->save();

            $this->registrarCambioEstado($contratacion, $estadoAnterior, $nuevoEstadoSlug);

            return [
                'estado' => $contratacion->estado,
                'estado_anterior' => $estadoAnterior,
                'fase_anterior' => $faseAnterior,
            ];
        } catch (\Exception $e) {
            Log::error('Error cambiando estado de contratación: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'nuevo_estado' => $nuevoEstadoSlug,
            ]);
            throw $e;
        }
    }

    /**
     * Cambiar uno o varios estados OPx de la contratación y registrar en historial.
     *
     * @param  array<string>  $codigos  Códigos OPx (ej. ['OP1-Documentacion', 'OP2-PendienteDeCobro'])
     * @param  bool  $replace  Si true, reemplaza todos los OPx por estos; si false, añade sin quitar los existentes
     * @return array{codigos: array<string>, ids: array<int>}
     */
    public function cambiarEstadosOPx(Contratacion $contratacion, array $codigos, bool $replace = false): array
    {
        // Obtener los estados OPx anteriores de la contratación
        $codigosAnteriores = $contratacion->estadosContratacion->pluck('codigo')->sort()->values()->all();

        // Sincronizar los estados OPx de la contratación
        $resultado = $this->estadoContratacionService->syncEstadosByCodigos($contratacion, $codigos, $replace);

        // Obtener los estados OPx nuevos de la contratación
        $codigosNuevos = $contratacion->fresh()->estadosContratacion->pluck('codigo')->sort()->values()->all();

        // Registrar el cambio de estados OPx en el historial
        $this->registrarCambioEstadosOPx($contratacion, $codigosAnteriores, $codigosNuevos);

        // Devolver los estados actuales de la contratación
        return $resultado;
    }

    /**
     * Registrar el cambio de estado en el historial (legacy estado/fase).
     */
    protected function registrarCambioEstado(
        Contratacion $contratacion,
        string $estadoAnterior,
        string $nuevoEstado
    ): void {
        try {
            HistorialActividad::create([
                'contratacion_id' => $contratacion->id,
                'fecha_inicio' => Carbon::now(),
                'actividad' => "Cambio de estado: '{$estadoAnterior}' → '{$nuevoEstado}'",
                'observaciones' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando cambio de estado en historial: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'estado_anterior' => $estadoAnterior,
                'nuevo_estado' => $nuevoEstado,
            ]);
        }
    }

    /**
     * Registrar en el historial el cambio de estados OPx.
     */
    protected function registrarCambioEstadosOPx(Contratacion $contratacion, array $codigosAnteriores, array $codigosNuevos): void
    {

        try {
            $antes = empty($codigosAnteriores) ? '(ninguno)' : implode(', ', $codigosAnteriores);
            $despues = empty($codigosNuevos) ? '(ninguno)' : implode(', ', $codigosNuevos);
            $actividad = "Estados OPx: [{$antes}] → [{$despues}]";

            HistorialActividad::create([
                'contratacion_id' => $contratacion->id,
                'fecha_inicio' => Carbon::now(),
                'actividad' => $actividad,
                'observaciones' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando cambio de estados OPx en historial: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'codigos_anteriores' => $codigosAnteriores,
                'codigos_nuevos' => $codigosNuevos,
            ]);
        }
    }
}
