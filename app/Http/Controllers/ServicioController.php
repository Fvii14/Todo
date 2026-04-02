<?php

namespace App\Http\Controllers;

use App\Services\ServicioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServicioController extends Controller
{
    protected ServicioService $servicioService;

    public function __construct(ServicioService $servicioService)
    {
        $this->servicioService = $servicioService;
    }

    /**
     * Obtener todos los servicios
     */
    public function index(): JsonResponse
    {
        try {
            $servicios = $this->servicioService->getAllServicios();

            return response()->json([
                'success' => true,
                'servicios' => $servicios,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ServicioController@index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los servicios',
            ], 500);
        }
    }

    /**
     * Crear un nuevo servicio
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'icono' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'orden' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $servicio = $this->servicioService->createServicio($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado correctamente',
                'servicio' => $servicio,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error en ServicioController@store: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el servicio',
            ], 500);
        }
    }

    /**
     * Obtener un servicio por ID
     */
    public function show(string $id): JsonResponse
    {
        try {
            $servicio = $this->servicioService->getServicioById($id);

            return response()->json([
                'success' => true,
                'servicio' => $servicio,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ServicioController@show: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado',
            ], 404);
        }
    }

    /**
     * Actualizar un servicio existente
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'descripcion' => 'nullable|string',
                'icono' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'orden' => 'nullable|integer|min:0',
                'activo' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $servicio = $this->servicioService->updateServicio($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado correctamente',
                'servicio' => $servicio,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ServicioController@update: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el servicio',
            ], 500);
        }
    }

    /**
     * Eliminar un servicio
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->servicioService->deleteServicio($id);

            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ServicioController@destroy: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el servicio',
            ], 500);
        }
    }
}
