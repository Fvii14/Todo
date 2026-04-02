<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
/* use App\Models\CrmTask; */
use App\Services\UserAyudaInactividadService;
/* use Carbon\Carbon; */
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoringController extends Controller
{
    protected UserAyudaInactividadService $scoringService;

    public function __construct(UserAyudaInactividadService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Obtiene el scoring completo de todas las user_ayudas
     */
    /*public function index(Request $request): JsonResponse
    {
        try {
            $resultados = $this->scoringService->calcularScoring();

            return response()->json([
                'success' => true,
                'data' => $resultados,
                'total' => count($resultados),
                'message' => 'Scoring calculado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculando scoring: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene el scoring de una user_ayuda específica
     */
    /*public function show(int $userAyudaId): JsonResponse
    {
        try {
            $resultado = $this->scoringService->obtenerScoringIndividual($userAyudaId);

            if (! $resultado) {
                return response()->json([
                    'success' => false,
                    'message' => 'UserAyuda no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => 'Scoring individual obtenido exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo scoring individual: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas del scoring
     */
    /*public function estadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->scoringService->obtenerEstadisticasScoring();

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas obtenidas exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene el scoring de un usuario específico por su user_id
     */
    /*public function porUsuario(int $userId): JsonResponse
    {
        try {
            $resultado = $this->scoringService->obtenerScoringPorUsuario($userId);

            if (! $resultado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado o sin scoring',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => 'Scoring del usuario obtenido exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo scoring del usuario: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene user_ayudas ordenadas por prioridad
     */
    /*public function prioridad(Request $request): JsonResponse
    {
        try {
            $limite = (int) $request->get('limite', 50);
            $limite = max(1, min($limite, 1000)); // Limitar entre 1 y 1000

            $resultados = $this->scoringService->obtenerUserAyudasPorPrioridad($limite);

            return response()->json([
                'success' => true,
                'data' => $resultados,
                'total' => count($resultados),
                'limite' => $limite,
                'message' => 'Prioridades calculadas exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculando prioridades: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene user_ayudas filtradas por rango de scoring
     */
    /*public function filtrarPorScore(Request $request): JsonResponse
    {
        try {
            $minScore = (float) $request->get('min_score', 0);
            $maxScore = (float) $request->get('max_score', 1000);
            $limite = (int) $request->get('limite', 50);

            // Obtener scoring completo
            $scoringCompleto = $this->scoringService->calcularScoring();

            // Filtrar por rango de score
            $filtrados = array_filter($scoringCompleto, function ($item) use ($minScore, $maxScore) {
                $score = $item['scoring']['score_total'];

                return $score >= $minScore && $score <= $maxScore;
            });

            // Ordenar por score descendente y limitar resultados
            usort($filtrados, function ($a, $b) {
                return $b['scoring']['score_total'] <=> $a['scoring']['score_total'];
            });

            $resultados = array_slice($filtrados, 0, $limite);

            return response()->json([
                'success' => true,
                'data' => $resultados,
                'total' => count($resultados),
                'total_filtrados' => count($filtrados),
                'filtros' => [
                    'min_score' => $minScore,
                    'max_score' => $maxScore,
                    'limite' => $limite,
                ],
                'message' => 'Filtrado por score completado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtrando por score: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Filtra user_ayudas por estado comercial
     */
    /*public function filtrarPorEstado(Request $request): JsonResponse
    {
        try {
            $estado = $request->get('estado');
            $limite = (int) $request->request->get('limite', 50);

            if (! in_array($estado, ['caliente', 'tibio', 'frio'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado no válido. Debe ser: caliente, tibio, o frio',
                ], 400);
            }

            // Obtener scoring completo
            $scoringCompleto = $this->scoringService->calcularScoring();

            // Filtrar por estado comercial
            $filtrados = array_filter($scoringCompleto, function ($item) use ($estado) {
                return $item['estado_comercial'] === $estado;
            });

            // Ordenar por score descendente y limitar resultados
            usort($filtrados, function ($a, $b) {
                return $b['scoring']['score_total'] <=> $a['scoring']['score_total'];
            });

            $resultados = array_slice($filtrados, 0, $limite);

            return response()->json([
                'success' => true,
                'data' => $resultados,
                'total' => count($resultados),
                'total_filtrados' => count($filtrados),
                'filtros' => [
                    'estado' => $estado,
                    'limite' => $limite,
                ],
                'message' => 'Filtrado por estado completado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtrando por estado: '.$e->getMessage(),
            ], 500);
        }
    }*/

    /**
     * Calcula scoring y crea tareas CRM automáticamente
     */
    /*public function crearTareasCRM(Request $request): JsonResponse
    {
        try {
            $limite = (int) $request->get('limite', 20);
            $assignedTo = $request->get('assigned_to'); // ID del usuario asignado (opcional)

            // Validar límite
            if ($limite < 1 || $limite > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Límite debe estar entre 1 y 100',
                ], 400);
            }

            // Ejecutar proceso de scoring y creación de tareas
            $resultado = $this->scoringService->calcularScoringYCrearTareasCRM($limite, $assignedTo);

            if (! $resultado['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => $resultado['message'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creando tareas CRM: '.$e->getMessage(),
            ], 500);
        }
    }*/

    /**
     * Obtiene estadísticas de las tareas CRM creadas
     */
    /*public function estadisticasTareasCRM(): JsonResponse
    {
        try {
            // Obtener estadísticas de tareas CRM
            $totalTareas = CrmTask::count();
            $tareasPendientes = CrmTask::where('status', 'pendiente')->count();
            $tareasCompletadas = CrmTask::where('status', 'completada')->count();
            $tareasCanceladas = CrmTask::where('status', 'cancelada')->count();

            // Obtener tareas creadas hoy
            $tareasHoy = CrmTask::whereDate('created_at', Carbon::now()->toDateString())->count();

            // Obtener tareas por prioridad (basado en título)
            $tareasCriticas = CrmTask::where('title', 'like', '%🚨%')->where('status', 'pendiente')->count();
            $tareasAltas = CrmTask::where('title', 'like', '%⚡%')->where('status', 'pendiente')->count();
            $tareasMedias = CrmTask::where('title', 'like', '%📞%')->where('status', 'pendiente')->count();

            $estadisticas = [
                'total_tareas' => $totalTareas,
                'por_estado' => [
                    'pendientes' => $tareasPendientes,
                    'completadas' => $tareasCompletadas,
                    'canceladas' => $tareasCanceladas,
                ],
                'tareas_hoy' => $tareasHoy,
                'por_prioridad' => [
                    'criticas' => $tareasCriticas,
                    'altas' => $tareasAltas,
                    'medias' => $tareasMedias,
                ],
                'ultimas_tareas' => CrmTask::with(['user:id,name,email'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(['id', 'user_id', 'title', 'status', 'created_at']),
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas de tareas CRM obtenidas exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas de tareas CRM: '.$e->getMessage(),
            ], 500);
        }
    }*/
}
