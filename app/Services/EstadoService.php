<?php

namespace App\Services;

use App\Models\Estado;
use App\Models\EstadoContratacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EstadoService
{
    /**
     * Obtener todos los estados disponibles ordenados por nombre
     */
    public function getAllEstados(): Collection
    {
        try {
            return Estado::getAllOrdered();
        } catch (\Exception $e) {
            Log::error('Error obteniendo estados: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener todos los estados OPx disponibles ordenados por código
     */
    public function getAllEstadosOPx(): Collection
    {
        try {
            return EstadoContratacion::orderBy('codigo')->get();
        } catch (\Exception $e) {
            Log::error('Error obteniendo estados OPx: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener un estado por slug
     */
    public function getEstadoBySlug(string $slug): ?Estado
    {
        try {
            return Estado::findBySlug($slug);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estado por slug: '.$e->getMessage(), ['slug' => $slug]);
            throw $e;
        }
    }

    /**
     * Obtener un estado por ID
     */
    public function getEstadoById(int $id): Estado
    {
        try {
            return Estado::findOrFail($id);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estado por ID: '.$e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Verificar si un slug de estado es válido
     */
    public function isValidEstadoSlug(string $slug): bool
    {
        try {
            return Estado::slugExists($slug);
        } catch (\Exception $e) {
            Log::error('Error verificando slug de estado: '.$e->getMessage(), ['slug' => $slug]);

            return false;
        }
    }

    /**
     * Obtener todos los slugs de estados válidos
     */
    public function getValidEstadoSlugs(): array
    {
        try {
            return Estado::getAllSlugs();
        } catch (\Exception $e) {
            Log::error('Error obteniendo slugs de estados: '.$e->getMessage());
            throw $e;
        }
    }
}
