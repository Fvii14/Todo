<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FormProcessingService
{
    public function __construct(
        private AnswerService $answerService,
        private QuestionService $questionService,
        private LocationService $locationService
    ) {}

    /**
     * Procesa y transforma el valor de una respuesta según el tipo de pregunta
     */
    public function processAnswerValue(Question $question, mixed $answer, int $userId): mixed
    {
        if ($question->type === 'select') {
            return $this->processSelectAnswer($question, $answer, $userId);
        } elseif ($question->type === 'multiple') {
            return $this->processMultipleAnswer($question, $answer);
        }

        // Para otros tipos (string, integer, etc.), retornar tal cual
        return $answer;
    }

    /**
     * Procesa respuesta de tipo select
     */
    private function processSelectAnswer(Question $question, mixed $answer, int $userId): ?string
    {
        $options = $this->questionService->getQuestionOptions($question);

        if ($question->slug === 'provincia') {
            $provincias = $this->locationService->getProvincias()->pluck('nombre_provincia', 'id')->toArray();
            $answerText = $provincias[$answer] ?? null;

            // Guardar también la CCAA relacionada
            if ($answer) {
                $idCcaa = $this->locationService->getCcaaIdByProvincia($answer);
                if ($idCcaa) {
                    // Eliminar respuesta anterior de CCAA
                    $this->answerService->deleteAnswersByUserAndQuestion($userId, 38);

                    // Crear nueva respuesta de CCAA
                    $this->answerService->createAnswer([
                        'user_id' => $userId,
                        'conviviente_id' => null,
                        'question_id' => 38, // pregunta comunidad autónoma
                        'answer' => $idCcaa,
                    ]);
                }
            }

            return $answerText;
        } elseif ($question->slug === 'municipio') {
            $municipioNombre = $this->locationService->getMunicipioNombreById($answer);

            return $municipioNombre ?? null;
        } elseif ($question->slug === 'estado_civil') {
            return is_numeric($answer) ? (string) ($answer + 1) : $answer;
        } elseif ($question->slug === 'sexo') {
            return $answer === 1 ? 'M' : 'H';
        } elseif ($question->slug === 'comunidad_autonoma') {
            return $answer;
        } else {
            // Obtener la opción por el ID
            return $options[$answer] ?? null;
        }
    }

    /**
     * Procesa respuesta de tipo multiple
     */
    private function processMultipleAnswer(Question $question, mixed $answer): string
    {
        $options = $this->questionService->getQuestionOptions($question);
        $answerText = [];

        if (is_array($answer)) {
            // Si el valor es un array con un solo valor y ese valor es una cadena con comas
            $answerValues = explode(',', $answer[0]);

            // Mapear esos valores para obtener las opciones correspondientes
            $answerText = array_map(function ($id) use ($options) {
                return $options[$id] ?? null;
            }, $answerValues);
        } else {
            // Si solo hay una opción seleccionada
            $answerText = [$options[$answer] ?? null];
        }

        return json_encode($answerText);
    }

    /**
     * Prepara preguntas para la vista con respuestas previas y validaciones
     */
    public function prepareQuestionsForView(Collection $questions, Collection $answers, Collection $regex, ?int $questionnaireId = null): Collection
    {
        return $questions->map(function ($q) use ($answers, $regex, $questionnaireId) {
            if ($q->slug === 'fecha_nacimiento' && isset($answers[$q->id]) && (int) $questionnaireId !== 42) {
                return null;
            }

            $regexItem = $regex->get($q->id);
            $options = $this->questionService->getQuestionOptions($q);
            $subtextWithLink = $this->questionService->formatQuestionSubtext($q->sub_text);

            // Obtener respuesta previa
            $answer = $answers[$q->id] ?? null;

            // Traducir ID a nombre visible si es provincia o municipio
            if ($q->slug === 'provincia' && $answer) {
                $answer = $options[$answer] ?? $answer;
            } elseif ($q->slug === 'municipio' && $answer) {
                $municipioNombre = $this->locationService->getMunicipioNombreById($answer);
                $answer = $municipioNombre ?? $answer;
            }

            return [
                'id' => $q->id,
                'text' => $q->text,
                'subtext' => $q->sub_text,
                'subtext_with_link' => $subtextWithLink,
                'type' => $q->type,
                'options' => $options,
                'integer_with_range' => $q->integer_with_range ?? false,
                'answer' => $answer,
                'disable_answer' => $q->disable_answer,
                'validation' => [
                    'pattern' => $regexItem?->pattern,
                    'error_message' => $regexItem?->error_message,
                ],
            ];
        })->filter()->values();
    }

    /**
     * Procesa y guarda todas las respuestas del formulario
     */
    public function processAndSaveAnswers(array $answers, int $userId): void
    {
        // Sincronizar respuestas especiales
        $this->answerService->syncSpecialAnswers($userId, $answers);

        // Filtrar respuestas nulas o vacías
        $filteredAnswers = array_filter($answers, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($filteredAnswers)) {
            return;
        }

        $questionIds = array_keys($filteredAnswers);

        $questions = $this->questionService->getQuestionsByIds($questionIds);

        $this->answerService->deleteAnswersByUserAndQuestions($userId, $questionIds);

        foreach ($filteredAnswers as $questionId => $answer) {
            $question = $questions->get($questionId);
            if (! $question) {
                Log::warning("Pregunta ID {$questionId} no encontrada");

                continue;
            }

            // Procesar el valor según el tipo de pregunta
            $processedValue = $this->processAnswerValue($question, $answer, $userId);

            // Guardar la respuesta solo si el valor procesado no es null
            if ($processedValue !== null) {
                $answerValue = is_string($processedValue) ? $processedValue : (is_array($processedValue) ? json_encode($processedValue) : $processedValue);
                $this->answerService->updateOrCreateAnswer(
                    [
                        'user_id' => $userId,
                        'question_id' => $questionId,
                        'conviviente_id' => null,
                    ],
                    [
                        'answer' => $answerValue,
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Procesa respuestas calculadas para cuestionario COLLECTOR
     */
    public function processCollectorCalculatedAnswers(int $userId, array $answers): void
    {
        $fechaNacimientoSlug = 'fecha_nacimiento';
        $generoSlug = 'genero';
        $fechaNacimientoId = $this->questionService->getQuestionIdBySlug($fechaNacimientoSlug);
        $generoId = $this->questionService->getQuestionIdBySlug($generoSlug);

        if (! $fechaNacimientoId) {
            Log::warning("No se encontró pregunta con slug '{$fechaNacimientoSlug}'");

            return;
        }

        // Obtener fecha de nacimiento
        $fechaNacimiento = $this->answerService->getAnswerByUserAndQuestion($userId, $fechaNacimientoId);
        $edad = $fechaNacimiento && $fechaNacimiento->answer
            ? \Carbon\Carbon::parse($fechaNacimiento->answer)->age
            : null;

        if ($edad === null) {
            Log::warning("No se pudo calcular la edad para usuario {$userId}");

            return;
        }

        // Preparar respuestas calculadas
        $calculatedAnswers = [
            [
                'user_id' => $userId,
                'question_id' => $this->questionService->getQuestionIdBySlug('tiene_menos_31_years'),
                'answer' => $edad <= 31,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'question_id' => $this->questionService->getQuestionIdBySlug('eres_padre_madre'),
                'answer' => match ($answers[$generoId] ?? null) {
                    '0' => 'Padre',
                    '1' => 'Madre',
                    default => '',
                },
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'question_id' => $this->questionService->getQuestionIdBySlug('edad'),
                'answer' => $edad,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'question_id' => $this->questionService->getQuestionIdBySlug('fecha_collector'),
                'answer' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Upsert de respuestas calculadas
        $this->answerService->upsertAnswers($calculatedAnswers, $userId);

        // Guardar fecha_formulario_inicial
        $fechaFormularioInicialId = $this->questionService->getQuestionIdBySlug('fecha_formulario_inicial');
        if ($fechaFormularioInicialId) {
            $this->answerService->upsertAnswers([
                [
                    'user_id' => $userId,
                    'question_id' => $fechaFormularioInicialId,
                    'answer' => \Carbon\Carbon::now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], $userId);
        }
    }
}
