<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use Illuminate\Support\Facades\DB;

class solicitudFormularioService
{
    protected CuestionarioCompletoService $cuestionarioCompletoService;

    protected FormConditionEvaluator $conditionEvaluator;

    public function __construct(
        CuestionarioCompletoService $cuestionarioCompletoService,
        FormConditionEvaluator $conditionEvaluator
    ) {
        $this->cuestionarioCompletoService = $cuestionarioCompletoService;
        $this->conditionEvaluator = $conditionEvaluator;
    }

    /**
     * Procesa toda la información del formulario de solicitud
     */
    public function obtenerDatosSolicitud($userId, $ayudaSolicitada, $gruposVulnerablesSeleccionados, $solicitudQuestionnaire = null)
    {
        // 1. Obtener questionnaire de tipo "solicitud"
        if ($solicitudQuestionnaire) {
            $solicitudQuestionnaireId = $solicitudQuestionnaire->id;
        } else {
            $ayudaId = $ayudaSolicitada->ayuda->id;
            $solicitudQuestionnaireId = Questionnaire::where('ayuda_id', $ayudaId)
                ->where('tipo', 'solicitud')
                ->value('id');
        }

        if (! $solicitudQuestionnaireId) {
            return null;
        }

        // 2. Comprobación de cuestionario completo
        $estadoPrincipal = $this->cuestionarioCompletoService
            ->usuarioPrincipalTieneCuestionarioCompleto($userId, $solicitudQuestionnaireId);

        // Guardar el ID en el objeto
        $ayudaSolicitada->solicitud_questionnaire_id = $solicitudQuestionnaireId;

        // 3. Obtener IDs de preguntas del cuestionario
        $questionIds = DB::table('questionnaire_questions')
            ->where('questionnaire_id', $solicitudQuestionnaireId)
            ->pluck('question_id');

        // 4. Respuestas actuales
        $answers = Answer::where('user_id', $userId)
            ->whereNull('conviviente_id')
            ->whereIn('question_id', $questionIds)
            ->pluck('answer', 'question_id')
            ->map(function ($answer) {
                $decoded = json_decode($answer, true);

                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
            });

        // 5. Preguntas ordenadas
        $questions = Question::whereIn('questions.id', $questionIds)
            ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->where('questionnaire_questions.questionnaire_id', $solicitudQuestionnaireId)
            ->orderBy('questionnaire_questions.orden')
            ->select('questions.*', 'questionnaire_questions.orden')
            ->get();

        // 6. Condiciones del formulario
        $conditions = QuestionCondition::getConditions($solicitudQuestionnaireId);

        $ayudaSolicitada->conditions_solicitud = $conditions;

        // 7. Regex de validación
        $regex = DB::table('regex')
            ->join('questions', 'questions.regex_id', '=', 'regex.id')
            ->whereIn('questions.id', $questionIds)
            ->select('questions.id as question_id', 'regex.pattern', 'regex.error_message')
            ->get()
            ->keyBy('question_id');

        // 8. Filtrar grupos vulnerables si el usuario no seleccionó ninguno
        /*if (empty($gruposVulnerablesSeleccionados)) {
            $questions = $questions->reject(
                fn ($q) => in_array($q->id, [172, 173, 183, 182])
            )->values();
        }*/

        // 8. Mapear preguntas al formato usado en el front de solicitud
        $mappedQuestions = $questions->map(function ($q) use ($answers, $regex) {
            // Inicializar $options con un array vacío por defecto
            $options = [];

            /*if ($q->slug === 'grupo_considerado_vulnerable') {
                $options = array_combine($gruposVulnerablesSeleccionados, $gruposVulnerablesSeleccionados);
            } else*/ if (is_string($q->options)) {
                $options = json_decode($q->options, true) ?? [];
            } elseif (is_array($q->options)) {
                $options = $q->options;
            }

            return [
                'id' => $q->id,
                'text' => $q->text,
                'subtext' => $q->sub_text,
                'type' => $q->type,
                'options' => $options,
                'answer' => $answers[$q->id] ?? null,
                'disable_answer' => $q->disable_answer,
                'validation' => [
                    'pattern' => $regex[$q->id]->pattern ?? null,
                    'error_message' => $regex[$q->id]->error_message ?? null,
                ],
            ];
        })->values();

        /**
         * TRADUCTOR DE SALTOS A VISIBILIDAD (SOLICITANTE - FORMULARIO REAL)
         *
         * En el editor visual (Vue Flow) las filas de `question_conditions` se interpretan
         * como "saltos" entre preguntas. Para el formulario real de solicitud necesitamos
         * traducir esos saltos a lógica de visibilidad (qué preguntas se muestran / ocultan),
         * igual que hacemos en el wizard y en el modal "Probar condiciones".
         *
         * Reglas básicas:
         * - Saltos directos (next_question_id = ID de la pregunta):
         *     La pregunta destino es VISIBLE si AL MENOS uno de esos saltos se cumple.
         * - Saltos que "saltan sobre" una pregunta (A → C saltándose B):
         *     La pregunta intermedia (B) es VISIBLE solo si NINGUNO de esos saltos se cumple.
         * - Si una pregunta no está afectada por ningún salto, es visible por defecto.
         */
        $visibilityByQuestionId = $this->calcularVisibilidadSolicitud(
            $mappedQuestions->all(),
            $conditions,
            $answers->toArray()
        );

        // IMPORTANTE: Enviar TODAS las preguntas al frontend, no solo las visibles.
        // El frontend necesita todas las preguntas en el DOM para construir correctamente
        // el array solicitudOrder y manejar la visibilidad condicional dinámicamente.
        // Incluimos la visibilidad inicial en cada pregunta para que el frontend sepa
        // cuáles mostrar inicialmente.
        $allQuestionsWithVisibility = $mappedQuestions->map(function (array $q) use ($visibilityByQuestionId) {
            $q['initial_visibility'] = $visibilityByQuestionId[$q['id']] ?? true;

            return $q;
        })->values();

        return [
            'estado' => $estadoPrincipal,
            'preguntas' => $allQuestionsWithVisibility,
            'solicitudQuestionnaireId' => $solicitudQuestionnaireId,
            'conditions' => $conditions,
        ];
    }

    public function obtenerDatosSolicitudOptimizado(
        $userId,
        $ayudaSolicitada,
        $gruposVulnerablesSeleccionados,
        int $solicitudQuestionnaireId,
        array $conditionsPrecargadas = []
    ) {
        $estadoPrincipal = $this->cuestionarioCompletoService
            ->usuarioPrincipalTieneCuestionarioCompleto($userId, $solicitudQuestionnaireId);

        $ayudaSolicitada->solicitud_questionnaire_id = $solicitudQuestionnaireId;

        $questionIds = DB::table('questionnaire_questions')
            ->where('questionnaire_id', $solicitudQuestionnaireId)
            ->pluck('question_id');

        $answers = Answer::where('user_id', $userId)
            ->whereNull('conviviente_id')
            ->whereIn('question_id', $questionIds)
            ->pluck('answer', 'question_id')
            ->map(function ($answer) {
                $decoded = json_decode($answer, true);

                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
            });

        $questions = Question::whereIn('questions.id', $questionIds)
            ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->where('questionnaire_questions.questionnaire_id', $solicitudQuestionnaireId)
            ->orderBy('questionnaire_questions.orden')
            ->select('questions.*', 'questionnaire_questions.orden')
            ->get();

        $conditions = ! empty($conditionsPrecargadas)
            ? $conditionsPrecargadas
            : QuestionCondition::getConditions($solicitudQuestionnaireId);

        $ayudaSolicitada->conditions_solicitud = $conditions;

        $regex = DB::table('regex')
            ->join('questions', 'questions.regex_id', '=', 'regex.id')
            ->whereIn('questions.id', $questionIds)
            ->select('questions.id as question_id', 'regex.pattern', 'regex.error_message')
            ->get()
            ->keyBy('question_id');

        $mappedQuestions = $questions->map(function ($q) use ($answers, $regex) {
            $options = [];

            if (is_string($q->options)) {
                $options = json_decode($q->options, true) ?? [];
            } elseif (is_array($q->options)) {
                $options = $q->options;
            }

            return [
                'id' => $q->id,
                'text' => $q->text,
                'subtext' => $q->sub_text,
                'type' => $q->type,
                'options' => $options,
                'answer' => $answers[$q->id] ?? null,
                'disable_answer' => $q->disable_answer,
                'validation' => [
                    'pattern' => $regex[$q->id]->pattern ?? null,
                    'error_message' => $regex[$q->id]->error_message ?? null,
                ],
            ];
        });

        return [
            'estado' => $estadoPrincipal,
            'preguntas' => $mappedQuestions,
            'solicitudQuestionnaireId' => $solicitudQuestionnaireId,
            'conditions' => $conditions,
        ];
    }

    /**
     * Calcula la visibilidad de cada pregunta del formulario de solicitud
     * a partir de los saltos configurados en `question_conditions` y de las
     * respuestas actuales del usuario.
     *
     * Devuelve un array [question_id => bool].
     */
    protected function calcularVisibilidadSolicitud(array $questions, array $conditions, array $answers): array
    {
        // Índice rápido de orden de preguntas: question_id => índice
        $order = [];
        foreach ($questions as $index => $q) {
            $order[$q['id']] = $index;
        }

        $totalQuestions = count($questions);

        $visibilityByQuestionId = [];

        foreach ($questions as $q) {
            $questionId = $q['id'];
            $questionIndex = $order[$questionId] ?? -1;

            if ($questionIndex === -1) {
                // Si por alguna razón no está en el índice, la dejamos visible
                $visibilityByQuestionId[$questionId] = true;

                continue;
            }

            // Saltos directos a esta pregunta (next_question_id = id actual)
            // Usar comparación suelta (==) en lugar de estricta (===) porque next_question_id
            // viene como string de DB::table() mientras que $questionId es un entero
            $jumpsTo = array_values(array_filter($conditions, function ($cond) use ($questionId) {
                return ($cond['next_question_id'] ?? null) == $questionId;
            }));

            // Saltos que "saltan sobre" esta pregunta (A → C pasando por ella)
            $jumpsSkipping = array_values(array_filter(
                $conditions,
                function ($jump) use ($questionIndex, $order, $totalQuestions) {
                    return $this->saltoSaltaPreguntaSolicitud($questionIndex, $jump, $order, $totalQuestions);
                }
            ));

            // Si no hay saltos que la afecten → visible por defecto
            if (empty($jumpsTo) && empty($jumpsSkipping)) {
                $visibilityByQuestionId[$questionId] = true;

                continue;
            }

            // NOTA: los saltos DIRECTOS (next_question_id = pregunta destino) NO condicionan
            // la visibilidad de la propia pregunta destino. Solo se usan para "saltar" las
            // preguntas intermedias. Por eso aquí SOLO usamos los saltos que la saltan.

            // Saltos que la saltan: visible solo si NINGUNO se cumple
            if (! empty($jumpsSkipping)) {
                $allSkippingInactive = true;
                foreach ($jumpsSkipping as $jump) {
                    $jumpResult = $this->evaluarCondicionSimpleSolicitud($jump, $answers);

                    if ($jumpResult) {
                        $allSkippingInactive = false;
                        break;
                    }
                }

                $visibilityByQuestionId[$questionId] = $allSkippingInactive;

                continue;
            }

            // Si solo tiene saltos directos (ninguno la "salta"), la pregunta es siempre visible.
            $visibilityByQuestionId[$questionId] = true;
        }

        return $visibilityByQuestionId;
    }

    /**
     * Determina si un salto "salta sobre" una pregunta (la omite en el flujo),
     * replicando la lógica de isQuestionSkippedByJump en el wizard.
     */
    protected function saltoSaltaPreguntaSolicitud(int $questionIndex, array $jump, array $order, int $totalQuestions): bool
    {
        $sourceId = $jump['question_id'] ?? null;
        $nextId = $jump['next_question_id'] ?? null;

        if ($sourceId === null) {
            return false;
        }

        $sourceIndex = $order[$sourceId] ?? -1;

        // Destino: si es null/FIN → después de la última pregunta
        if ($nextId === null || $nextId === 'FIN' || $nextId === '') {
            $destIndex = $totalQuestions;
        } else {
            $destIndex = $order[$nextId] ?? -1;
        }

        if ($sourceIndex === -1 || $destIndex === -1) {
            return false;
        }

        // Si el salto va de una pregunta anterior a una posterior y la actual está entre ambas,
        // entonces el salto la "salta".
        return $questionIndex > $sourceIndex && $questionIndex < $destIndex;
    }

    /**
     * Evalúa una condición simple (sin rules anidados) contra las respuestas del usuario.
     * Usa las columnas: question_id, operator, value.
     */
    protected function evaluarCondicionSimpleSolicitud(array $condition, array $answers): bool
    {
        $questionId = $condition['question_id'] ?? null;

        if ($questionId === null) {
            return false;
        }

        $answer = $answers[$questionId] ?? null;

        // Si falta value u operator, no podemos evaluar nada
        if (! array_key_exists('value', $condition) || $condition['value'] === null) {
            return false;
        }

        // Si no hay respuesta, la condición no se cumple (igual que en el modal)
        if ($answer === null || $answer === '') {
            return false;
        }

        $operator = $condition['operator'] ?? '==';
        $expectedValue = $condition['value'];

        return $this->conditionEvaluator->evaluate($answer, $operator, $expectedValue);
    }
}
