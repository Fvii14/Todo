<?php

namespace App\Services;

use App\Models\Contratacion;
use App\Models\EstadoContratacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EstadoContratacionService
{
    /**
     * Obtener todos los estados OPx ordenados por código.
     */
    public function getAll(): Collection
    {
        try {
            return EstadoContratacion::orderBy('codigo')->get();
        } catch (\Exception $e) {
            Log::error('Error obteniendo estados_contratacion: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener estados por grupo (OP1, OP2, OP3, OP4, OP5...).
     */
    public function getByGrupo(string $grupo): Collection
    {
        try {
            return EstadoContratacion::where('grupo', $grupo)
                ->orderBy('codigo')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error obteniendo estados_contratacion por grupo: '.$e->getMessage(), [
                'grupo' => $grupo,
            ]);
            throw $e;
        }
    }

    /**
     * Obtener un estado por código OPx (ej. OP1-Documentacion).
     * Esta funcion devuelve un estado OPx por su código de la tabla estados_contratacion.
     */
    public function getByCodigo(string $codigo): ?EstadoContratacion
    {
        try {
            return EstadoContratacion::where('codigo', $codigo)->first();
        } catch (\Exception $e) {
            Log::error('Error obteniendo estado_contratacion por código: '.$e->getMessage(), [
                'codigo' => $codigo,
            ]);
            throw $e;
        }
    }

    /**
     * Obtener todos los códigos OPx válidos.
     */
    public function getValidCodigos(): array
    {
        try {
            return EstadoContratacion::pluck('codigo')->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo códigos de estados_contratacion: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Sincronizar estados OPx de una contratación a partir de códigos.
     *
     * @param  array<string>  $codigos  Lista de códigos OPx (ej. ['OP1-Documentacion','OP2-PendienteDeCobro'])
     * @param  bool  $replace  Si true, reemplaza todos los estados OPx actuales por estos.
     *                         Si false, solo añade sin eliminar los existentes.
     * @return array{codigos: array<string>, ids: array<int>}
     */
    public function syncEstadosByCodigos(Contratacion $contratacion, array $codigos, bool $replace = false): array
    {
        // Limpiar array de códigos (strings no vacíos y únicos)
        $codigos = collect($codigos)
            ->filter(fn ($c) => is_string($c) && trim($c) !== '')
            ->map(fn ($c) => trim($c))
            ->unique()
            ->values()
            ->all();

        if (empty($codigos)) {
            if ($replace) {
                // Si se pide reemplazar con lista vacía, limpiamos todos los estados OPx
                $contratacion->estadosContratacion()->detach();
            }

            return ['codigos' => [], 'ids' => []];
        }

        try {
            // Recuperar IDs para los códigos proporcionados
            $estados = EstadoContratacion::whereIn('codigo', $codigos)
                ->pluck('id', 'codigo');

            // Detectar códigos inválidos
            $codigosInvalidos = array_diff($codigos, $estados->keys()->all());
            if (! empty($codigosInvalidos)) {
                throw new \InvalidArgumentException(
                    'Códigos de estados_contratacion no válidos: '.implode(', ', $codigosInvalidos)
                );
            }

            $ids = $estados->values()->all();

            if ($replace) {
                // Reemplazar todos los estados OPx existentes por estos
                $contratacion->estadosContratacion()->sync($ids);
            } else {
                // Añadir sin eliminar los existentes
                $contratacion->estadosContratacion()->syncWithoutDetaching($ids);
            }

            return [
                'codigos' => $codigos,
                'ids' => $ids,
            ];
        } catch (\Exception $e) {
            Log::error('Error sincronizando estados_contratacion: '.$e->getMessage(), [
                'contratacion_id' => $contratacion->id,
                'codigos' => $codigos,
            ]);
            throw $e;
        }
    }
}
