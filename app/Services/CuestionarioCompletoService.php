<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Conviviente;
use App\Models\QuestionCondition;
use App\Models\QuestionnaireQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CuestionarioCompletoService
{
    private FormConditionEvaluator $conditionEvaluator;

    public function __construct(FormConditionEvaluator $conditionEvaluator)
    {
        $this->conditionEvaluator = $conditionEvaluator;
    }

    /**
     * Evalúa si el usuario principal tiene el cuestionario completo.
     *
     * @param  int  $userId  ID del usuario
     * @param  int  $questionnaireId  ID del cuestionario
     * @return array ['completo' => bool, 'faltantes' => array]
     */
    public function usuarioPrincipalTieneCuestionarioCompleto(int $userId, int $questionnaireId): array
    {
        $answers = Answer::where('user_id', $userId)
            ->whereNull('conviviente_id')
            ->get()
            ->keyBy('question_id');

        return $this->evaluarCuestionario($answers, $questionnaireId, 'user_id: '.$userId);
    }

    /**
     * Evalúa si todos los convivientes tienen el cuestionario completo.
     *
     * @param  int  $userId  ID del usuario
     * @param  int  $questionnaireId  ID del cuestionario
     * @return array ['completo' => bool, 'faltantes_por_conviviente' => array]
     */
    public function convivientesTienenCuestionarioCompleto(int $userId, int $questionnaireId): array
    {
        // Obtener número esperado de convivientes
        $respuestaNumeroConvivientes = Answer::where('user_id', $userId)
            ->where('question_id', 5) // pregunta del número total de personas
            ->whereNull('conviviente_id')
            ->first();

        $numeroConvivientes = (int) ($respuestaNumeroConvivientes->answer ?? 0) - 1; // restamos al usuario principal

        if ($numeroConvivientes <= 0) {
            return [
                'completo' => true,
                'faltantes_por_conviviente' => [],
            ];
        }

        // Obtener los convivientes registrados
        $convivientes = Conviviente::where('user_id', $userId)->get();

        // Evaluar cuestionario de cada conviviente existente
        $faltantesPorConviviente = [];
        $hayConvivientesFaltantes = false;

        if ($convivientes->count() < $numeroConvivientes) {
            $hayConvivientesFaltantes = true;
            // Añadir mensaje informativo, pero continuar evaluando los convivientes existentes
            $faltantesPorConviviente['_faltantes_por_crear'] = "Faltan convivientes por registrar: se esperaban $numeroConvivientes, pero hay {$convivientes->count()} creados.";
        }

        // Evaluar los formularios de los convivientes existentes
        foreach ($convivientes as $conviviente) {
            $answers = Answer::where('conviviente_id', $conviviente->id)
                ->get()
                ->keyBy('question_id');

            $resultado = $this->evaluarCuestionario($answers, $questionnaireId, 'conviviente_id: '.$conviviente->id);

            if (! $resultado['completo']) {
                $faltantesPorConviviente[$conviviente->id] = $resultado['faltantes'];
            }
        }

        // El estado está completo solo si:
        // 1. No faltan convivientes por crear Y
        // 2. Todos los convivientes existentes tienen el formulario completo
        $completo = ! $hayConvivientesFaltantes && empty($faltantesPorConviviente);

        return [
            'completo' => $completo,
            'faltantes_por_conviviente' => $faltantesPorConviviente,
        ];
    }

    /**
     * Evalúa un cuestionario dado un conjunto de respuestas.
     *
     * @param  \Illuminate\Support\Collection  $answers  Respuestas del cuestionario, indexadas por question_id
     * @param  int  $questionnaireId  ID del cuestionario
     * @param  string  $contextoLog  Contexto del log
     * @return array ['completo' => bool, 'faltantes' => array]
     */
    private function evaluarCuestionario(Collection $answers, int $questionnaireId, string $contextoLog): array
    {
        $preguntas = QuestionnaireQuestion::where('questionnaire_id', $questionnaireId)
            ->where('is_visible', 1)
            ->with(['question']) // importante: accedemos a los campos del modelo Question
            ->get();

        $faltantes = [];

        foreach ($preguntas as $qq) {
            $question = $qq->question;

            // Omitir si no requiere respuesta
            if (
                $question->type === 'info' ||
                $question->disable_answer
            ) {
                continue;
            }

            // Comprobar condiciones de visibilidad
            $conditions = QuestionCondition::where('questionnaire_id', $questionnaireId)
                ->where('next_question_id', $question->id)
                ->get();

            $mostrar = true;

            foreach ($conditions as $cond) {
                // Usar solo el formato nuevo (operator + value)
                if (! $cond->operator || $cond->value === null) {
                    // No hay condición válida
                    Log::warning("Condición malformada en question_id {$cond->question_id} para $contextoLog (sin operator/value)");

                    continue;
                }

                $operator = $cond->operator;
                $value = $cond->value;

                // Normalizar operador: convertir '=' a '=='
                if ($operator === '=') {
                    $operator = '==';
                }

                // Convertir a formato de array de valores esperados para compatibilidad
                $expectedValues = is_array($value) ? $value : [$value];

                $answerModel = $answers->get($cond->question_id);
                $respuesta = $answerModel?->answer;

                // Normalizar la respuesta: convertir strings numéricos a enteros
                // Manejar null: si la condición no incluye 0, null significa que no se cumple
                $respuestaNormalizada = null;

                if ($respuesta === null || $respuesta === '') {
                    // Si la condición incluye 0, tratar null/vacío como 0
                    // Si la condición espera [1] y la respuesta es null, la condición NO se cumple
                    if (is_array($expectedValues) && in_array(0, $expectedValues, true)) {
                        $respuestaNormalizada = 0;
                    } else {
                        // La respuesta es null/vacía y la condición no incluye 0, por lo tanto no se cumple
                        $mostrar = false;
                        break;
                    }
                } elseif (is_numeric($respuesta)) {
                    // Convertir string numérico a int para comparación consistente
                    $respuestaNormalizada = (int) $respuesta;
                } else {
                    $respuestaNormalizada = $respuesta;
                }

                $expectedValuesNormalizados = array_map(function ($val) {
                    return is_numeric($val) ? (int) $val : $val;
                }, $expectedValues);

                // Si el operador es null, usar '==' por defecto
                $operador = $cond->operator ?? '==';

                // Usar el evaluador centralizado para comparar respuesta vs valores esperados
                $mostrar = $this->conditionEvaluator->evaluate(
                    $respuestaNormalizada,
                    $operador,
                    $expectedValuesNormalizados
                );

                // Si la condición no se cumple, salir del bucle (lógica AND: todas deben cumplirse)
                if (! $mostrar) {
                    break;
                }
            }

            // Log final del estado de visibilidad

            if (! $mostrar) {
                continue;
            }

            // ❌ Si no hay respuesta, marcar como faltante
            if (! $answers->has($question->id)) {
                $faltantes[] = $question->slug;
            }
        }

        return [
            'completo' => count($faltantes) === 0,
            'faltantes' => $faltantes,
        ];
    }

    /**
     * Evalúa si un conviviente específico tiene el cuestionario completo.
     * Considera condiciones de visibilidad y todas las preguntas del cuestionario.
     *
     * @param  int  $convivienteId  ID del conviviente
     * @param  int  $questionnaireId  ID del cuestionario
     * @return array ['completo' => bool, 'faltantes' => array]
     */
    public function convivienteTieneCuestionarioCompleto(int $convivienteId, int $questionnaireId): array
    {
        $answers = Answer::where('conviviente_id', $convivienteId)
            ->get()
            ->keyBy('question_id');

        return $this->evaluarCuestionario($answers, $questionnaireId, 'conviviente_id: '.$convivienteId);
    }

    /**
     * Comprueba si un conviviente ha completado las preguntas finales obligatorias.
     * Devuelve true si todas las preguntas tienen respuesta, false si falta alguna.
     *
     * @param  int  $userId  ID del usuario
     * @param  int  $convivienteId  ID del conviviente
     * @param  array  $preguntasFinales  Preguntas finales obligatorias
     * @return bool True si todas las preguntas tienen respuesta, false si falta alguna
     */
    public function comprobarConvivienteCompleto(int $userId, int $convivienteId, array $preguntasFinales): bool
    {
        if (empty($preguntasFinales)) {
            return true;
        }

        $respuestas = Answer::where('user_id', $userId)
            ->where('conviviente_id', $convivienteId)
            ->whereIn('question_id', $preguntasFinales)
            ->pluck('answer', 'question_id');

        foreach ($preguntasFinales as $questionId) {
            if (! isset($respuestas[$questionId]) || $respuestas[$questionId] === null || $respuestas[$questionId] === '') {
                return false;
            }
        }

        return true;
    }
}
