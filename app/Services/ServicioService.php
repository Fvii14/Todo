<?php

namespace App\Services;

use App\Models\Servicio;
use Illuminate\Support\Facades\Log;

class ServicioService
{
    /**
     * Obtener todos los servicios activos
     */
    public function getAllServicios(): array
    {
        try {
            $servicios = Servicio::where('activo', true)
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get();

            return $servicios->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo servicios: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener un servicio por ID
     */
    public function getServicioById(string $id): Servicio
    {
        return Servicio::findOrFail($id);
    }

    /**
     * Crear un nuevo servicio
     */
    public function createServicio(array $data): Servicio
    {
        try {
            $servicio = Servicio::create([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'icono' => $data['icono'] ?? 'fas fa-check-circle',
                'color' => $data['color'] ?? '#ef4444', // Rojo por defecto
                'orden' => $data['orden'] ?? 0,
                'activo' => true,
            ]);

            return $servicio;
        } catch (\Exception $e) {
            Log::error('Error creando servicio: '.$e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    /**
     * Actualizar un servicio existente
     */
    public function updateServicio(string $id, array $data): Servicio
    {
        try {
            $servicio = Servicio::findOrFail($id);
            
            // Solo actualizar los campos que están presentes en el request
            $updateData = [];
            $allowedFields = ['nombre', 'descripcion', 'icono', 'color', 'orden', 'activo'];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            $servicio->update($updateData);

            return $servicio->fresh();
        } catch (\Exception $e) {
            Log::error('Error actualizando servicio: '.$e->getMessage(), [
                'servicio_id' => $id,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar un servicio
     */
    public function deleteServicio(string $id): void
    {
        try {
            $servicio = Servicio::findOrFail($id);
            $servicio->delete();
        } catch (\Exception $e) {
            Log::error('Error eliminando servicio: '.$e->getMessage(), [
                'servicio_id' => $id,
            ]);
            throw $e;
        }
    }
}
