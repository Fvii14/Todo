<?php

namespace App\Services;

use App\Models\Ayuda;
use App\Models\ComunicacionOperativa;
use App\Models\MailTracking;
use App\Models\UserAyuda;
use Carbon\Carbon;

class UserAyudaInactividadService
{
    /**
     * Ejecuta el proceso de bajadas por inactividad
     */
    public function ejecutarBajadasPorInactividad(): array
    {
        $resultados = [
            'caliente_a_tibio' => 0,
            'tibio_a_frio' => 0,
            'errores' => 0,
            'total_procesadas' => 0,
        ];

        try {

            // Procesar bajadas de caliente a tibio (> 7 días)
            $resultados['caliente_a_tibio'] = $this->procesarBajadaCalienteATibio();

            // Procesar bajadas de tibio a frio (> 14 días)
            $resultados['tibio_a_frio'] = $this->procesarBajadaTibioAFrio();

            $resultados['total_procesadas'] = $resultados['caliente_a_tibio'] + $resultados['tibio_a_frio'];

        } catch (\Exception $e) {
            $resultados['errores']++;
        }

        return $resultados;
    }

    /**
     * Calcula el scoring para todas las user_ayudas
     */
    public function calcularScoring(): array
    {
        try {

            $userAyudas = UserAyuda::with(['ayuda'])->get();
            $resultados = [];

            foreach ($userAyudas as $userAyuda) {
                $scoring = $this->calcularScoringIndividual($userAyuda);
                $resultados[] = [
                    'user_ayuda_id' => $userAyuda->id,
                    'user_id' => $userAyuda->user_id,
                    'ayuda_id' => $userAyuda->ayuda_id,
                    'estado_comercial' => $userAyuda->estado_comercial,
                    'scoring' => $scoring,
                ];
            }

            // Ordenar por scoring descendente
            usort($resultados, function ($a, $b) {
                return $b['scoring']['score_total'] <=> $a['scoring']['score_total'];
            });

            return $resultados;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calcula el scoring individual para una user_ayuda
     */
    private function calcularScoringIndividual(UserAyuda $userAyuda): array
    {
        $urgenciaDeadline = $this->calcularUrgenciaDeadline($userAyuda);
        $temperatura = $this->calcularTemperatura($userAyuda);
        $valorEstimadoNorm = $this->calcularValorEstimadoNorm($userAyuda);
        $penalizacionInactividad = $this->calcularPenalizacionInactividad($userAyuda);

        $scoreTotal = $urgenciaDeadline + $temperatura + $valorEstimadoNorm - $penalizacionInactividad;

        return [
            'urgencia_deadline' => $urgenciaDeadline,
            'temperatura' => $temperatura,
            'valor_estimado_norm' => $valorEstimadoNorm,
            'penalizacion_inactividad' => $penalizacionInactividad,
            'score_total' => $scoreTotal,
            'detalles' => [
                'dias_restantes' => $this->obtenerDiasRestantes($userAyuda),
                'ultimo_contacto' => $this->obtenerUltimaActividad($userAyuda->user_id, $userAyuda->ayuda_id),
                'valor_estimado' => $userAyuda->ayuda?->cuantia_usuario ?? 0,
            ],
        ];
    }

    /**
     * Calcula la urgencia basada en el deadline
     * ≤3d=50, ≤7d=30, ≤15d=15, >15d=0
     */
    private function calcularUrgenciaDeadline(UserAyuda $userAyuda): int
    {
        if (! $userAyuda->ayuda) {
            return 0;
        }

        $diasRestantes = $this->obtenerDiasRestantes($userAyuda);

        if ($diasRestantes <= 0) {
            return 0; // Ya expiró
        } elseif ($diasRestantes <= 3) {
            return 50;
        } elseif ($diasRestantes <= 7) {
            return 30;
        } elseif ($diasRestantes <= 15) {
            return 15;
        } else {
            return 0;
        }
    }

    /**
     * Calcula la temperatura basada en el estado comercial
     * frio=0, tibio=10, caliente=25
     */
    private function calcularTemperatura(UserAyuda $userAyuda): int
    {
        return match ($userAyuda->estado_comercial) {
            'frio' => 0,
            'tibio' => 10,
            'caliente' => 25,
            default => 0
        };
    }

    /**
     * Calcula el valor estimado normalizado
     * min(valor_estimado/200, 20)
     */
    private function calcularValorEstimadoNorm(UserAyuda $userAyuda): float
    {
        if (! $userAyuda->ayuda || ! $userAyuda->ayuda->cuantia_usuario) {
            return 0;
        }

        $valorEstimado = $userAyuda->ayuda->cuantia_usuario;
        $valorNormalizado = $valorEstimado / 200;

        return min($valorNormalizado, 20);
    }

    /**
     * Calcula la penalización por inactividad
     * floor(dias_sin_contacto/3) (tope 20)
     */
    private function calcularPenalizacionInactividad(UserAyuda $userAyuda): int
    {
        $ultimaActividad = $this->obtenerUltimaActividad($userAyuda->user_id, $userAyuda->ayuda_id);

        if (! $ultimaActividad) {
            // Si no hay actividad, penalización máxima
            return 20;
        }

        $diasSinContacto = Carbon::now()->diffInDays($ultimaActividad);
        $penalizacion = floor($diasSinContacto / 3);

        return min($penalizacion, 20);
    }

    /**
     * Obtiene los días restantes hasta el deadline
     */
    private function obtenerDiasRestantes(UserAyuda $userAyuda): int
    {
        if (! $userAyuda->ayuda) {
            return 0;
        }

        $fechaDeadline = null;

        // Si la fecha_inicio ya pasó, usar fecha_fin
        if ($userAyuda->ayuda->fecha_inicio && Carbon::now()->gt($userAyuda->ayuda->fecha_inicio)) {
            $fechaDeadline = $userAyuda->ayuda->fecha_fin;
        } else {
            $fechaDeadline = $userAyuda->ayuda->fecha_inicio;
        }

        if (! $fechaDeadline) {
            return 0;
        }

        return Carbon::now()->diffInDays($fechaDeadline, false);
    }

    /**
     * Procesa las bajadas de caliente a tibio (> 7 días sin actividad)
     */
    private function procesarBajadaCalienteATibio(): int
    {
        $fechaLimite = Carbon::now()->subDays(7);
        $contador = 0;

        try {
            // Obtener user_ayudas con estado_comercial = 'caliente' y sin actividad reciente
            $userAyudas = UserAyuda::where('estado_comercial', 'caliente')
                ->where(function ($query) {
                    $query->whereNull('ayuda_id')
                        ->orWhereNotNull('ayuda_id');
                })
                ->get();

            foreach ($userAyudas as $userAyuda) {
                if ($this->debeBajarATibio($userAyuda, $fechaLimite)) {
                    $userAyuda->estado_comercial = 'tibio';
                    $userAyuda->save();
                    $contador++;

                }
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return $contador;
    }

    /**
     * Procesa las bajadas de tibio a frio (> 14 días sin actividad)
     */
    private function procesarBajadaTibioAFrio(): int
    {
        $fechaLimite = Carbon::now()->subDays(14);
        $contador = 0;

        try {
            // Obtener user_ayudas con estado_comercial = 'tibio' y sin actividad reciente
            $userAyudas = UserAyuda::where('estado_comercial', 'tibio')
                ->where(function ($query) {
                    $query->whereNull('ayuda_id')
                        ->orWhereNotNull('ayuda_id');
                })
                ->get();

            foreach ($userAyudas as $userAyuda) {
                if ($this->debeBajarAFrio($userAyuda, $fechaLimite)) {
                    $userAyuda->estado_comercial = 'frio';
                    $userAyuda->save();
                    $contador++;

                }
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return $contador;
    }

    /**
     * Determina si una user_ayuda debe bajar de caliente a tibio
     */
    private function debeBajarATibio(UserAyuda $userAyuda, Carbon $fechaLimite): bool
    {
        $ultimaActividad = $this->obtenerUltimaActividad($userAyuda->user_id, $userAyuda->ayuda_id);

        return $ultimaActividad === null || $ultimaActividad->lt($fechaLimite);
    }

    /**
     * Determina si una user_ayuda debe bajar de tibio a frio
     */
    private function debeBajarAFrio(UserAyuda $userAyuda, Carbon $fechaLimite): bool
    {
        $ultimaActividad = $this->obtenerUltimaActividad($userAyuda->user_id, $userAyuda->ayuda_id);

        return $ultimaActividad === null || $ultimaActividad->lt($fechaLimite);
    }

    /**
     * Obtiene la fecha de la última actividad para un usuario y ayuda específicos
     */
    private function obtenerUltimaActividad(int $userId, ?int $ayudaId): ?Carbon
    {
        try {
            // Buscar en mail_tracking
            $ultimoMail = MailTracking::where('user_id', $userId)
                ->when($ayudaId, function ($query, $ayudaId) {
                    return $query->where('ayuda_id', $ayudaId);
                })
                ->orderBy('sent_at', 'desc')
                ->first();

            // Buscar en comunicaciones_operativa
            $ultimaComunicacion = ComunicacionOperativa::where('user_id', $userId)
                ->orderBy('fecha_hora', 'desc')
                ->first();

            // Determinar cuál es la más reciente
            $fechaMail = $ultimoMail ? $ultimoMail->sent_at : null;
            $fechaComunicacion = $ultimaComunicacion ? $ultimaComunicacion->fecha_hora : null;

            if ($fechaMail && $fechaComunicacion) {
                return $fechaMail->gt($fechaComunicacion) ? $fechaMail : $fechaComunicacion;
            }

            return $fechaMail ?? $fechaComunicacion;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene estadísticas de las bajadas por inactividad
     */
    public function obtenerEstadisticas(): array
    {
        try {
            $estadisticas = [
                'total_user_ayudas' => UserAyuda::count(),
                'por_estado' => [
                    'caliente' => UserAyuda::where('estado_comercial', 'caliente')->count(),
                    'tibio' => UserAyuda::where('estado_comercial', 'tibio')->count(),
                    'frio' => UserAyuda::where('estado_comercial', 'frio')->count(),
                    'sin_estado' => UserAyuda::whereNull('estado_comercial')->count(),
                ],
                'con_ayuda_id' => UserAyuda::whereNotNull('ayuda_id')->count(),
                'sin_ayuda_id' => UserAyuda::whereNull('ayuda_id')->count(),
            ];

            return $estadisticas;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene el scoring para una user_ayuda específica
     */
    public function obtenerScoringIndividual(int $userAyudaId): ?array
    {
        try {
            $userAyuda = UserAyuda::with(['ayuda'])->find($userAyudaId);

            if (! $userAyuda) {
                return null;
            }

            $scoring = $this->calcularScoringIndividual($userAyuda);

            return [
                'user_ayuda_id' => $userAyuda->id,
                'user_id' => $userAyuda->user_id,
                'ayuda_id' => $userAyuda->ayuda_id,
                'estado_comercial' => $userAyuda->estado_comercial,
                'scoring' => $scoring,
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene el scoring para un usuario específico por su user_id
     */
    public function obtenerScoringPorUsuario(int $userId): ?array
    {
        try {
            $userAyudas = UserAyuda::with(['ayuda'])
                ->where('user_id', $userId)
                ->get();

            if ($userAyudas->isEmpty()) {
                return null;
            }

            // Obtener la user_ayuda con mayor scoring
            $mejorUserAyuda = null;
            $mayorScore = -1;

            foreach ($userAyudas as $userAyuda) {
                $scoring = $this->calcularScoringIndividual($userAyuda);
                if ($scoring['score_total'] > $mayorScore) {
                    $mayorScore = $scoring['score_total'];
                    $mejorUserAyuda = $userAyuda;
                }
            }

            if (! $mejorUserAyuda) {
                return null;
            }

            $scoring = $this->calcularScoringIndividual($mejorUserAyuda);

            return [
                'user_ayuda_id' => $mejorUserAyuda->id,
                'user_id' => $mejorUserAyuda->user_id,
                'ayuda_id' => $mejorUserAyuda->ayuda_id,
                'estado_comercial' => $mejorUserAyuda->estado_comercial,
                'scoring' => $scoring,
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene estadísticas del scoring
     */
    public function obtenerEstadisticasScoring(): array
    {
        try {
            $scoringCompleto = $this->calcularScoring();

            if (empty($scoringCompleto)) {
                return [];
            }

            $scores = array_column($scoringCompleto, 'scoring');
            $scoreTotales = array_column($scores, 'score_total');
            $urgencias = array_column($scores, 'urgencia_deadline');
            $temperaturas = array_column($scores, 'temperatura');
            $valoresEstimados = array_column($scores, 'valor_estimado_norm');
            $penalizaciones = array_column($scores, 'penalizacion_inactividad');

            $estadisticas = [
                'total_user_ayudas' => count($scoringCompleto),
                'score_promedio' => round(array_sum($scoreTotales) / count($scoreTotales), 2),
                'score_maximo' => max($scoreTotales),
                'score_minimo' => min($scoreTotales),
                'por_rango_score' => [
                    'alto' => count(array_filter($scoreTotales, fn ($score) => $score >= 50)),
                    'medio' => count(array_filter($scoreTotales, fn ($score) => $score >= 20 && $score < 50)),
                    'bajo' => count(array_filter($scoreTotales, fn ($score) => $score < 20)),
                ],
                'componentes_promedio' => [
                    'urgencia_deadline' => round(array_sum($urgencias) / count($urgencias), 2),
                    'temperatura' => round(array_sum($temperaturas) / count($temperaturas), 2),
                    'valor_estimado_norm' => round(array_sum($valoresEstimados) / count($valoresEstimados), 2),
                    'penalizacion_inactividad' => round(array_sum($penalizaciones) / count($penalizaciones), 2),
                ],
                'top_10_por_score' => array_slice($scoringCompleto, 0, 10),
            ];

            return $estadisticas;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene user_ayudas ordenadas por scoring (prioridad)
     */
    public function obtenerUserAyudasPorPrioridad(int $limite = 50): array
    {
        try {
            $scoringCompleto = $this->calcularScoring();

            if (empty($scoringCompleto)) {
                return [];
            }

            $resultados = array_slice($scoringCompleto, 0, $limite);

            foreach ($resultados as &$resultado) {
                $resultado['prioridad'] = $this->determinarPrioridad($resultado['scoring']['score_total']);
                $resultado['accion_recomendada'] = $this->determinarAccionRecomendada($resultado);
            }

            return $resultados;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Determina el nivel de prioridad basado en el score
     */
    private function determinarPrioridad(float $scoreTotal): string
    {
        if ($scoreTotal >= 60) {
            return 'CRÍTICA';
        } elseif ($scoreTotal >= 40) {
            return 'ALTA';
        } elseif ($scoreTotal >= 20) {
            return 'MEDIA';
        } else {
            return 'BAJA';
        }
    }

    /**
     * Determina la acción recomendada basada en el scoring
     */
    private function determinarAccionRecomendada(array $resultado): string
    {
        $scoring = $resultado['scoring'];

        if ($scoring['urgencia_deadline'] >= 50) {
            return 'CONTACTO INMEDIATO - Deadline crítico';
        } elseif ($scoring['urgencia_deadline'] >= 30) {
            return 'CONTACTO URGENTE - Deadline próximo';
        } elseif ($scoring['penalizacion_inactividad'] >= 15) {
            return 'REACTIVAR - Mucha inactividad';
        } elseif ($scoring['temperatura'] === 0) {
            return 'CALENTAR - Usuario frío';
        } elseif ($scoring['score_total'] >= 40) {
            return 'SEGUIMIENTO - Buena oportunidad';
        } else {
            return 'MANTENER - Baja prioridad';
        }
    }

    /**
     * Obtiene el nombre de la ayuda por su ID
     */
    private function obtenerNombreAyuda(int $ayudaId): string
    {
        try {
            $ayuda = \App\Models\Ayuda::find($ayudaId);
            if ($ayuda && $ayuda->nombre_ayuda) {
                return $ayuda->nombre_ayuda;
            }

            if ($ayuda) {
                return "Ayuda #{$ayudaId}";
            }

            return "Ayuda #{$ayudaId} (no encontrada)";
        } catch (\Exception $e) {
            return "Ayuda #{$ayudaId}";
        }
    }
}
