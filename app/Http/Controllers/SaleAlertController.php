<?php

namespace App\Http\Controllers;

use App\Models\SaleAlert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleAlertController extends Controller
{
    /**
     * Listar todas las alertas activas (ordenadas por fecha)
     */
    public function index(): JsonResponse
    {
        $alertas = SaleAlert::with('user')
            ->where('activa', true)
            ->orderBy('fecha_alerta', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'alertas' => $alertas,
        ]);
    }

    /**
     * Obtener alertas de un usuario específico
     */
    public function getByUser(User $user): JsonResponse
    {
        $alertas = $user->saleAlerts()
            ->orderBy('fecha_alerta', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'alertas' => $alertas,
        ]);
    }

    /**
     * Crear una nueva alerta
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'titulo' => 'required|string|max:255',
            'nota' => 'nullable|string',
            'fecha_alerta' => 'required|date|after:now',
        ]);

        try {
            $alerta = SaleAlert::create([
                'user_id' => $request->user_id,
                'titulo' => $request->titulo,
                'nota' => $request->nota,
                'fecha_alerta' => Carbon::parse($request->fecha_alerta),
                'activa' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alerta creada correctamente',
                'alerta' => $alerta->load('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la alerta: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar una alerta existente
     */
    public function update(Request $request, SaleAlert $saleAlert): JsonResponse
    {
        $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'nota' => 'nullable|string',
            'fecha_alerta' => 'sometimes|required|date',
            'activa' => 'sometimes|boolean',
        ]);

        try {
            $saleAlert->update($request->only(['titulo', 'nota', 'fecha_alerta', 'activa']));

            return response()->json([
                'success' => true,
                'message' => 'Alerta actualizada correctamente',
                'alerta' => $saleAlert->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la alerta: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una alerta
     */
    public function destroy(SaleAlert $saleAlert): JsonResponse
    {
        try {
            $saleAlert->delete();

            return response()->json([
                'success' => true,
                'message' => 'Alerta eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la alerta: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marcar alerta como completada (desactivar)
     */
    public function markAsCompleted(SaleAlert $saleAlert): JsonResponse
    {
        try {
            $saleAlert->update(['activa' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Alerta marcada como completada',
                'alerta' => $saleAlert->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar la alerta: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener alertas próximas (próximas 7 días)
     */
    public function getProximas(): JsonResponse
    {
        $alertas = SaleAlert::with('user')
            ->proximas()
            ->orderBy('fecha_alerta', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'alertas' => $alertas,
        ]);
    }

    /**
     * Obtener alertas vencidas
     */
    public function getVencidas(): JsonResponse
    {
        $alertas = SaleAlert::with('user')
            ->vencidas()
            ->orderBy('fecha_alerta', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'alertas' => $alertas,
        ]);
    }

    /**
     * Obtener estadísticas de alertas
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total' => SaleAlert::count(),
            'activas' => SaleAlert::activas()->count(),
            'proximas' => SaleAlert::proximas()->count(),
            'vencidas' => SaleAlert::vencidas()->count(),
            'completadas' => SaleAlert::where('activa', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
