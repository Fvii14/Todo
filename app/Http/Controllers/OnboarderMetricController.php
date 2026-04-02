<?php

namespace App\Http\Controllers;

use App\Models\Onboarder;
use App\Models\OnboarderMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnboarderMetricController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OnboarderMetric::with(['onboarder.user', 'section', 'convivienteType']);

        if ($request->has('onboarder_id')) {
            $query->where('onboarder_id', $request->onboarder_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $metrics = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    public function getSectionStats(): JsonResponse
    {
        $stats = OnboarderMetric::where('action', 'section_completed')
            ->whereNotNull('duration_seconds')
            ->selectRaw('
                section_id,
                AVG(duration_seconds) as avg_duration,
                MIN(duration_seconds) as min_duration,
                MAX(duration_seconds) as max_duration,
                COUNT(*) as completion_count
            ')
            ->with('section')
            ->groupBy('section_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getConvivienteTypeStats(): JsonResponse
    {
        $stats = OnboarderMetric::where('action', 'conviviente_completed')
            ->whereNotNull('duration_seconds')
            ->selectRaw('
                conviviente_type_id,
                AVG(duration_seconds) as avg_duration,
                MIN(duration_seconds) as min_duration,
                MAX(duration_seconds) as max_duration,
                COUNT(*) as completion_count
            ')
            ->with('convivienteType')
            ->groupBy('conviviente_type_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getAbandonmentStats(): JsonResponse
    {
        $abandonedOnboarders = Onboarder::where('status', 'abandoned')
            ->with('user')
            ->get();

        $abandonmentReasons = OnboarderMetric::whereIn('action', [
            'section_started', 'conviviente_started',
        ])
            ->whereNull('completed_at')
            ->with(['onboarder.user', 'section', 'convivienteType'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'abandoned_onboarders' => $abandonedOnboarders,
                'abandonment_reasons' => $abandonmentReasons,
            ],
        ]);
    }

    public function getActiveProgress(): JsonResponse
    {
        $activeOnboarders = Onboarder::whereIn('status', ['draft', 'in_progress'])
            ->with(['user', 'wizard', 'sections', 'convivientes'])
            ->get()
            ->map(function ($onboarder) {
                return [
                    'id' => $onboarder->id,
                    'user' => $onboarder->user->name ?? 'Usuario',
                    'wizard' => $onboarder->wizard->name ?? 'Wizard',
                    'status' => $onboarder->status,
                    'progress_percentage' => $onboarder->getProgressPercentage(),
                    'started_at' => $onboarder->started_at,
                    'sections_count' => $onboarder->sections->count(),
                    'convivientes_count' => $onboarder->convivientes->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activeOnboarders,
        ]);
    }

    public function getPerformanceMetrics(): JsonResponse
    {
        $totalOnboarders = Onboarder::count();
        $completedOnboarders = Onboarder::where('status', 'completed')->count();
        $abandonedOnboarders = Onboarder::where('status', 'abandoned')->count();
        $inProgressOnboarders = Onboarder::whereIn('status', ['draft', 'in_progress'])->count();

        $completionRate = $totalOnboarders > 0 ? ($completedOnboarders / $totalOnboarders) * 100 : 0;
        $abandonmentRate = $totalOnboarders > 0 ? ($abandonedOnboarders / $totalOnboarders) * 100 : 0;

        $avgCompletionTime = OnboarderMetric::where('action', 'section_completed')
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');

        return response()->json([
            'success' => true,
            'data' => [
                'total_onboarders' => $totalOnboarders,
                'completed_onboarders' => $completedOnboarders,
                'abandoned_onboarders' => $abandonedOnboarders,
                'in_progress_onboarders' => $inProgressOnboarders,
                'completion_rate' => round($completionRate, 2),
                'abandonment_rate' => round($abandonmentRate, 2),
                'avg_completion_time_seconds' => round($avgCompletionTime ?? 0, 2),
            ],
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarder_id' => 'required|exists:onboarders,id',
            'section_id' => 'nullable|exists:onboarder_sections,id',
            'conviviente_type_id' => 'nullable|exists:conviviente_types,id',
            'action' => 'required|string',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'duration_seconds' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $metric = OnboarderMetric::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Métrica creada correctamente',
                'data' => $metric,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la métrica',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
