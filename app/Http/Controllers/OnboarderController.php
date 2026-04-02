<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Conviviente;
use App\Models\Educacion;
use App\Models\Ingreso;
use App\Models\Municipio;
use App\Models\Onboarder;
use App\Models\OnboarderMetric;
use App\Models\Provincia;
use App\Models\Question;
use App\Models\Wizard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OnboarderController extends Controller
{
    public function getCompleted(): JsonResponse
    {
        $onboarder = Onboarder::where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->first();

        if (! $onboarder) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún onboarder completado',
            ], 404);
        }

        $onboarder->load([
            'wizard',
            'sections.questions.question',
            'convivienteTypes.sections.questions.question',
        ]);

        return response()->json([
            'success' => true,
            'data' => $onboarder,
        ]);
    }

    public function getUserAnswers(): JsonResponse
    {
        $userId = Auth::id();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
            ], 401);
        }

        $answers = Answer::where('user_id', $userId)
            ->with('question')
            ->get()
            ->keyBy('question_id');

        $formattedAnswers = [];
        foreach ($answers as $answer) {
            $formattedAnswers[$answer->question_id] = [
                'id' => $answer->id,
                'answer' => $answer->answer,
                'formatted_answer' => $answer->getFormattedAnswer(),
                'question_id' => $answer->question_id,
                'question_slug' => $answer->question->slug ?? null,
                'question_type' => $answer->question->type ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedAnswers,
        ]);
    }

    public function getOrCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'wizard_id' => 'required|exists:wizards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $wizardId = $request->wizard_id;
        $userId = Auth::id();

        Onboarder::query()->update(['status' => 'abandoned']);

        $wizard = Wizard::findOrFail($wizardId);
        $wizardData = $wizard->data ?? [];

        $onboarder = Onboarder::create([
            'wizard_id' => $wizardId,
            'user_id' => $userId,
            'status' => 'completed',
            'data' => [],
            'completed_at' => now(),
        ]);

        // Crear secciones del solicitante desde wizard.data
        if (isset($wizardData['sections'])) {
            foreach ($wizardData['sections'] as $index => $sectionData) {
                $section = $onboarder->sections()->create([
                    'name' => $sectionData['name'] ?? 'Sección '.($index + 1),
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionData['order'] ?? $index,
                    'skip_condition' => $sectionData['skip_condition'] ?? null,
                    'is_required' => $sectionData['is_required'] ?? true,
                    'is_skippeable' => $sectionData['is_skippeable'] ?? false,
                ]);

                // Crear preguntas de la sección
                if (isset($sectionData['questions'])) {
                    foreach ($sectionData['questions'] as $qIndex => $questionData) {
                        $section->questions()->create([
                            'onboarder_id' => $onboarder->id,
                            'question_id' => $questionData['question_id'],
                            'order' => $questionData['order'] ?? $qIndex,
                            'screen' => $questionData['screen'] ?? 0,
                            'condition' => $questionData['condition'] ?? null,
                            'required_condition' => $questionData['required_condition'] ?? null,
                            'optional_condition' => $questionData['optional_condition'] ?? null,
                            'block_if_bankflip_filled' => $questionData['block_if_bankflip_filled'] ?? false,
                            'is_builder' => $questionData['is_builder'] ?? false,
                            'conditional_options' => $questionData['conditional_options'] ?? null,
                        ]);
                    }
                }
            }
        }

        // Crear tipos de conviviente desde wizard.data
        if (isset($wizardData['conviviente_types'])) {
            foreach ($wizardData['conviviente_types'] as $typeIndex => $typeData) {
                $convivienteType = $onboarder->convivienteTypes()->create([
                    'name' => $typeData['name'] ?? 'Tipo '.($typeIndex + 1),
                    'description' => $typeData['description'] ?? null,
                    'icon' => $typeData['icon'] ?? null,
                    'order' => $typeData['order'] ?? $typeIndex,
                ]);

                // Crear secciones del conviviente
                if (isset($typeData['sections'])) {
                    foreach ($typeData['sections'] as $sectionIndex => $sectionData) {
                        $section = $convivienteType->sections()->create([
                            'onboarder_id' => $onboarder->id,
                            'name' => $sectionData['name'] ?? 'Sección '.($sectionIndex + 1),
                            'description' => $sectionData['description'] ?? null,
                            'order' => $sectionData['order'] ?? $sectionIndex,
                            'skip_condition' => $sectionData['skip_condition'] ?? null,
                            'is_required' => $sectionData['is_required'] ?? true,
                            'is_skippeable' => $sectionData['is_skippeable'] ?? false,
                        ]);

                        // Crear preguntas de la sección del conviviente
                        if (isset($sectionData['questions'])) {
                            foreach ($sectionData['questions'] as $qIndex => $questionData) {
                                $section->questions()->create([
                                    'onboarder_id' => $onboarder->id,
                                    'question_id' => $questionData['question_id'],
                                    'order' => $questionData['order'] ?? $qIndex,
                                    'screen' => $questionData['screen'] ?? 0,
                                    'condition' => $questionData['condition'] ?? null,
                                    'required_condition' => $questionData['required_condition'] ?? null,
                                    'optional_condition' => $questionData['optional_condition'] ?? null,
                                    'block_if_bankflip_filled' => $questionData['block_if_bankflip_filled'] ?? false,
                                    'is_builder' => $questionData['is_builder'] ?? false,
                                    'conditional_options' => $questionData['conditional_options'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $onboarder->load(['wizard', 'sections.questions.question', 'convivienteTypes.sections.questions.question']),
        ]);
    }

    public function getWizardConfig(int $wizardId): JsonResponse
    {
        $wizard = Wizard::with([
            'sections.questions.question',
            'convivienteTypes.sections.questions.question',
        ])->findOrFail($wizardId);

        return response()->json([
            'success' => true,
            'data' => $wizard,
        ]);
    }

    public function saveAnswer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarder_id' => 'required|exists:onboarders,id',
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required',
            'user_conviviente_id' => 'nullable|exists:user_convivientes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $onboarder = Onboarder::findOrFail($request->onboarder_id);

        if ($onboarder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $answer = Answer::where('onboarder_id', $onboarder->id)
                ->where('question_id', $request->question_id)
                ->where('user_conviviente_id', $request->user_conviviente_id)
                ->first();

            if ($answer) {
                $answer->update(['answer' => $request->answer]);
            } else {
                Answer::create([
                    'onboarder_id' => $onboarder->id,
                    'user_id' => Auth::id(),
                    'question_id' => $request->question_id,
                    'answer' => $request->answer,
                    'user_conviviente_id' => $request->user_conviviente_id,
                ]);
            }

            $data = $onboarder->data ?? [];
            $data['answers'][$request->question_id] = $request->answer;
            if ($request->user_conviviente_id) {
                $data['conviviente_answers'][$request->user_conviviente_id][$request->question_id] = $request->answer;
            }
            $onboarder->update(['data' => $data]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Respuesta guardada correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la respuesta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function completeSection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarder_id' => 'required|exists:onboarders,id',
            'section_id' => 'required|exists:onboarder_sections,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $onboarder = Onboarder::findOrFail($request->onboarder_id);

        if ($onboarder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        try {
            OnboarderMetric::createSectionCompleted($onboarder->id, $request->section_id);

            $onboarder->update(['current_section_id' => $request->section_id]);

            return response()->json([
                'success' => true,
                'message' => 'Sección completada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar la sección',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addConviviente(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarder_id' => 'required|exists:onboarders,id',
            'conviviente_type_id' => 'required|exists:conviviente_types,id',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $onboarder = Onboarder::findOrFail($request->onboarder_id);

        if ($onboarder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $userConviviente = $onboarder->convivientes()->create([
                'conviviente_type_id' => $request->conviviente_type_id,
                'data' => $request->data ?? [],
                'order' => $onboarder->convivientes()->count(),
            ]);

            OnboarderMetric::createConvivienteStarted($onboarder->id, $request->conviviente_type_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conviviente añadido correctamente',
                'data' => $userConviviente->load('convivienteType'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al añadir conviviente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function complete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarder_id' => 'required|exists:onboarders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $onboarder = Onboarder::findOrFail($request->onboarder_id);

        if ($onboarder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        try {
            $onboarder->markAsCompleted();

            return response()->json([
                'success' => true,
                'message' => 'Onboarder completado correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar el onboarder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMetrics(int $onboarderId): JsonResponse
    {
        $onboarder = Onboarder::findOrFail($onboarderId);

        $metrics = $onboarder->metrics()
            ->with(['section', 'convivienteType'])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    public function trackMetric(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarder_id' => 'required|integer|exists:onboarders,id',
            'action' => 'required|string|max:255',
            'section_id' => 'nullable|integer|exists:onboarder_sections,id',
            'section_type' => 'nullable|string|in:solicitante,conviviente',
            'conviviente_type_id' => 'nullable|integer|exists:conviviente_types,id',
            'conviviente_index' => 'nullable|integer',
            'duration_seconds' => 'nullable|integer|min:0',
            'from_section' => 'nullable|string',
            'to_section' => 'nullable|string',
            'last_section' => 'nullable|array',
            'screen_index' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            $metadata = [];
            if (isset($data['from_section'])) {
                $metadata['from_section'] = $data['from_section'];
            }
            if (isset($data['to_section'])) {
                $metadata['to_section'] = $data['to_section'];
            }
            if (isset($data['last_section'])) {
                $metadata['last_section'] = $data['last_section'];
            }
            if (isset($data['conviviente_index'])) {
                $metadata['conviviente_index'] = $data['conviviente_index'];
            }
            if (isset($data['screen_index'])) {
                $metadata['screen_index'] = $data['screen_index'];
            }

            $metric = OnboarderMetric::create([
                'onboarder_id' => $data['onboarder_id'],
                'section_id' => $data['section_id'] ?? null,
                'conviviente_type_id' => $data['conviviente_type_id'] ?? null,
                'action' => $data['action'],
                'started_at' => now(),
                'completed_at' => in_array($data['action'], ['onboarder_completed', 'section_completed', 'conviviente_completed', 'screen_completed']) ? now() : null,
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'metadata' => $metadata,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Métrica registrada correctamente',
                'data' => $metric,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la métrica',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function finishOnboarder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarder_id' => 'required|integer|exists:onboarders,id',
            'answers' => 'required|array',
            'convivientes' => 'nullable|array',
            'convivientes.*.tipo' => 'required|string',
            'convivientes.*.answers' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $answers = $request->input('answers', []);
            $convivientes = $request->input('convivientes', []);

            $now = now();
            $allQuestionIds = array_keys($answers);
            foreach ($convivientes as $convivienteData) {
                $allQuestionIds = array_merge($allQuestionIds, array_keys($convivienteData['answers'] ?? []));
            }

            $requiredSlugs = [
                'fecha_formulario_inicial',
                'ingresos_totales_uc',
                'provincia',
                'municipio',
                'comunidad_autonoma',
                'retribucion_trabajo',
                'base_imponible',
            ];

            $questionsById = Question::whereIn('id', array_unique($allQuestionIds))
                ->orWhereIn('slug', $requiredSlugs)
                ->get()
                ->keyBy('id');

            $questionsBySlug = $questionsById->keyBy('slug');

            $provinciasById = Provincia::all()->keyBy('id');
            $provinciasByName = Provincia::all()->keyBy('nombre_provincia');
            $municipiosById = Municipio::all()->keyBy('id');
            $municipiosByName = Municipio::all()->keyBy('nombre_municipio');

            $questionFechaFormulario = $questionsBySlug->get('fecha_formulario_inicial');

            if ($questionFechaFormulario) {
                unset($answers[$questionFechaFormulario->id]);
            }

            $allAnswersToUpsert = [];
            $allEducacionToInsert = [];
            $allIngresosToInsert = [];

            foreach ($answers as $questionId => $answer) {
                if ($answer !== null && $answer !== '') {
                    $question = $questionsById->get($questionId);
                    if ($question) {
                        $this->accumulateFormattedAnswer(
                            $allAnswersToUpsert,
                            $allEducacionToInsert,
                            $allIngresosToInsert,
                            $userId,
                            $questionId,
                            $answer,
                            null,
                            $question,
                            $questionsBySlug,
                            $provinciasById,
                            $provinciasByName,
                            $municipiosById,
                            $municipiosByName,
                            $now
                        );
                    }
                }
            }

            $this->accumulateComunidadAutonoma(
                $allAnswersToUpsert,
                $userId,
                null,
                $answers,
                $questionsBySlug,
                $provinciasById,
                $provinciasByName,
                $municipiosById,
                $municipiosByName,
                $now
            );

            $convivientesCreated = [];

            foreach ($convivientes as $index => $convivienteData) {
                $conviviente = Conviviente::create([
                    'user_id' => $userId,
                    'tipo' => $convivienteData['tipo'],
                    'index' => $index + 1,
                    'token' => uniqid(),
                ]);
                $convivientesCreated[] = $conviviente;

                foreach ($convivienteData['answers'] as $questionId => $answer) {
                    if ($answer !== null && $answer !== '') {
                        $question = $questionsById->get($questionId);
                        if ($question) {
                            $this->accumulateFormattedAnswer(
                                $allAnswersToUpsert,
                                $allEducacionToInsert,
                                $allIngresosToInsert,
                                $userId,
                                $questionId,
                                $answer,
                                $conviviente->id,
                                $question,
                                $questionsBySlug,
                                $provinciasById,
                                $provinciasByName,
                                $municipiosById,
                                $municipiosByName,
                                $now
                            );
                        }
                    }
                }

                $this->accumulateComunidadAutonoma(
                    $allAnswersToUpsert,
                    $userId,
                    $conviviente->id,
                    $convivienteData['answers'],
                    $questionsBySlug,
                    $provinciasById,
                    $provinciasByName,
                    $municipiosById,
                    $municipiosByName,
                    $now
                );
            }

            if ($questionFechaFormulario) {
                $allAnswersToUpsert[] = [
                    'user_id' => $userId,
                    'question_id' => $questionFechaFormulario->id,
                    'conviviente_id' => null,
                    'answer' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $totalNetIncome = $this->calculateTotalNetIncome($answers, $convivientes);

            $questionIngresosTotales = $questionsBySlug->get('ingresos_totales_uc');

            if ($questionIngresosTotales) {
                $allAnswersToUpsert[] = [
                    'user_id' => $userId,
                    'question_id' => $questionIngresosTotales->id,
                    'conviviente_id' => null,
                    'answer' => $totalNetIncome,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($allAnswersToUpsert)) {
                Answer::upsert(
                    $allAnswersToUpsert,
                    ['user_id', 'question_id', 'conviviente_id'],
                    ['answer', 'updated_at']
                );
            }

            if (! empty($allEducacionToInsert)) {
                Educacion::insert($allEducacionToInsert);
            }

            if (! empty($allIngresosToInsert)) {
                Ingreso::insert($allIngresosToInsert);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Respuestas guardadas correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las respuestas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function accumulateFormattedAnswer(
        array &$answersAccumulator,
        array &$educacionAccumulator,
        array &$ingresosAccumulator,
        int $userId,
        int $questionId,
        $answer,
        ?int $convivienteId,
        $question,
        $questionsBySlug,
        $provinciasById,
        $provinciasByName,
        $municipiosById,
        $municipiosByName,
        $now
    ): void {
        $formattedAnswer = $this->formatAnswerForAccumulation(
            $answer,
            $question,
            $userId,
            $convivienteId,
            $questionsBySlug,
            $answersAccumulator,
            $educacionAccumulator,
            $ingresosAccumulator,
            $provinciasById,
            $provinciasByName,
            $municipiosById,
            $municipiosByName,
            $now
        );

        $answersAccumulator[] = [
            'user_id' => $userId,
            'question_id' => $questionId,
            'conviviente_id' => $convivienteId,
            'answer' => $formattedAnswer,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function formatAnswerForAccumulation(
        $answer,
        $question,
        int $userId,
        ?int $convivienteId,
        $questionsBySlug,
        array &$answersAccumulator,
        array &$educacionAccumulator,
        array &$ingresosAccumulator,
        $provinciasById,
        $provinciasByName,
        $municipiosById,
        $municipiosByName,
        $now
    ) {
        switch ($question->type) {
            case 'boolean':
                if ($answer === true || $answer === 'true' || $answer === '1' || $answer === 'Sí') {
                    return '1';
                } elseif ($answer === false || $answer === 'false' || $answer === '0' || $answer === 'No') {
                    return '0';
                }

                return $answer;

            case 'date':
                if (is_string($answer)) {
                    try {
                        $date = new \DateTime($answer);

                        return $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        return $answer;
                    }
                }

                return $answer;

            case 'builder':
                return $this->formatBuilderAnswerForAccumulation(
                    $answer,
                    $question,
                    $userId,
                    $convivienteId,
                    $questionsBySlug,
                    $answersAccumulator,
                    $educacionAccumulator,
                    $ingresosAccumulator,
                    $provinciasById,
                    $provinciasByName,
                    $municipiosById,
                    $municipiosByName,
                    $now
                );

            case 'select':
            case 'multiple':
            case 'text':
            case 'string':
            case 'number':
            case 'integer':
            default:
                return (string) $answer;
        }
    }

    private function formatBuilderAnswerForAccumulation(
        $answer,
        $question,
        int $userId,
        ?int $convivienteId,
        $questionsBySlug,
        array &$answersAccumulator,
        array &$educacionAccumulator,
        array &$ingresosAccumulator,
        $provinciasById,
        $provinciasByName,
        $municipiosById,
        $municipiosByName,
        $now
    ) {
        $data = is_string($answer) ? json_decode($answer, true) : $answer;

        if ($question->slug === 'calculadora_ingresos') {
            if (isset($data['totalGrossIncome']) && isset($data['netIncome'])) {
                $retribucionQuestion = $questionsBySlug ? $questionsBySlug->get('retribucion_trabajo') : null;
                $baseImponibleQuestion = $questionsBySlug ? $questionsBySlug->get('base_imponible') : null;

                if ($retribucionQuestion) {
                    $answersAccumulator[] = [
                        'user_id' => $userId,
                        'question_id' => $retribucionQuestion->id,
                        'conviviente_id' => $convivienteId,
                        'answer' => (string) $data['totalGrossIncome'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($baseImponibleQuestion) {
                    $answersAccumulator[] = [
                        'user_id' => $userId,
                        'question_id' => $baseImponibleQuestion->id,
                        'conviviente_id' => $convivienteId,
                        'answer' => (string) $data['netIncome'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (isset($data['incomes']) && is_array($data['incomes'])) {
                foreach ($data['incomes'] as $income) {
                    $tipo = (string) ($income['type'] ?? '');
                    $meses = (int) ($income['months'] ?? 0);
                    $importeMedio = (float) ($income['amount'] ?? 0);
                    $importeAnual = (float) ($income['annual'] ?? ($meses * $importeMedio));

                    if ($tipo !== '' && $meses > 0 && $importeMedio > 0) {
                        $ingresosAccumulator[] = [
                            'user_id' => $userId,
                            'conviviente_id' => $convivienteId,
                            'tipo' => $tipo,
                            'meses' => $meses,
                            'importe_medio' => $importeMedio,
                            'importe_anual' => $importeAnual,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            return json_encode($data);
        }

        if ($question->slug === 'educacion') {
            $this->accumulateEducacionData(
                $data,
                $userId,
                $convivienteId,
                $educacionAccumulator,
                $provinciasById,
                $provinciasByName,
                $municipiosById,
                $municipiosByName,
                $now
            );

            return json_encode($data);
        }

        return is_string($answer) ? $answer : json_encode($answer);
    }

    private function accumulateEducacionData(
        $data,
        int $userId,
        ?int $convivienteId,
        array &$educacionAccumulator,
        $provinciasById,
        $provinciasByName,
        $municipiosById,
        $municipiosByName,
        $now
    ): void {
        if (is_object($data)) {
            $data = (array) json_decode(json_encode($data), true);
        }

        if (isset($data['conviviente_id'])) {
            $convivienteId = $data['conviviente_id'];
        }

        if (isset($data['studies']) && is_array($data['studies'])) {
            foreach ($data['studies'] as $estudio) {
                if (isset($estudio['isNoStudies']) && $estudio['isNoStudies'] === true) {
                    $payload = [
                        'user_id' => $userId,
                        'conviviente_id' => $convivienteId,
                        'tipo' => 'sin_estudios',
                        'institucion' => null,
                        'nombre_estudio' => null,
                        'nivel' => null,
                        'fecha_inicio' => null,
                        'fecha_fin' => null,
                        'descripcion' => null,
                        'provincia_id' => null,
                        'municipio_id' => null,
                        'ownership' => null,
                        'modality' => null,
                        'is_official' => null,
                        'is_enrolled' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } else {
                    $tipo = ($estudio['status'] === 'ongoing') ? 'en_curso' : 'finalizado';
                    $fechaInicio = isset($estudio['startDate']) ? $estudio['startDate'].'-01' : null;
                    $fechaFin = isset($estudio['endDate']) ? $estudio['endDate'].'-01' : null;

                    $provinciaId = $this->resolveProvinciaId(
                        $estudio['provincia'] ?? null,
                        $provinciasById,
                        $provinciasByName
                    );

                    $municipioId = $this->resolveMunicipioId(
                        $estudio['municipio'] ?? null,
                        $provinciaId,
                        $municipiosById,
                        $municipiosByName
                    );

                    $payload = [
                        'user_id' => $userId,
                        'conviviente_id' => $convivienteId,
                        'tipo' => $tipo,
                        'institucion' => $estudio['institution'] ?? '',
                        'nombre_estudio' => $estudio['name'] ?? '',
                        'nivel' => $estudio['level'] ?? '',
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'descripcion' => $estudio['modality'] ?? null,
                        'provincia_id' => $provinciaId,
                        'municipio_id' => $municipioId,
                        'ownership' => $estudio['ownership'] ?? null,
                        'modality' => $estudio['modality'] ?? null,
                        'is_official' => isset($estudio['isOfficial']) ? (bool) $estudio['isOfficial'] : null,
                        'is_enrolled' => isset($estudio['isEnrolled']) ? (bool) $estudio['isEnrolled'] : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $educacionAccumulator[] = $payload;
            }
        }
    }

    private function accumulateComunidadAutonoma(
        array &$answersAccumulator,
        int $userId,
        ?int $convivienteId,
        array $answers,
        $questionsBySlug,
        $provinciasById,
        $provinciasByName,
        $municipiosById,
        $municipiosByName,
        $now
    ): void {
        $provinciaQuestion = $questionsBySlug ? $questionsBySlug->get('provincia') : null;
        $municipioQuestion = $questionsBySlug ? $questionsBySlug->get('municipio') : null;
        $ccaaQuestion = $questionsBySlug ? $questionsBySlug->get('comunidad_autonoma') : null;

        if (! $ccaaQuestion) {
            return;
        }

        $ccaaId = null;

        if ($provinciaQuestion && isset($answers[$provinciaQuestion->id])) {
            $provinciaValue = $answers[$provinciaQuestion->id];

            if (is_numeric($provinciaValue)) {
                $provincia = $provinciasById ? $provinciasById->get($provinciaValue) : null;
            } else {
                if ($provinciasByName) {
                    $provincia = $provinciasByName->get($provinciaValue);
                    if (! $provincia) {
                        $provincia = $provinciasByName->first(function ($p) use ($provinciaValue) {
                            return stripos($p->nombre_provincia, $provinciaValue) !== false;
                        });
                    }
                } else {
                    $provincia = null;
                }
            }

            if ($provincia && $provincia->id_ccaa) {
                $ccaaId = $provincia->id_ccaa;
            }
        }

        if (! $ccaaId && $municipioQuestion && isset($answers[$municipioQuestion->id])) {
            $municipioValue = $answers[$municipioQuestion->id];
            if (is_numeric($municipioValue)) {
                $municipio = $municipiosById ? $municipiosById->get($municipioValue) : null;
            } else {
                if ($municipiosByName) {
                    $municipio = $municipiosByName->get($municipioValue);
                    if (! $municipio) {
                        $municipio = $municipiosByName->first(function ($m) use ($municipioValue) {
                            return stripos($m->nombre_municipio, $municipioValue) !== false;
                        });
                    }
                } else {
                    $municipio = null;
                }
            }

            if ($municipio && $municipio->provincia_id) {
                $provincia = $provinciasById ? $provinciasById->get($municipio->provincia_id) : null;
                if ($provincia && $provincia->id_ccaa) {
                    $ccaaId = $provincia->id_ccaa;
                }
            }
        }

        if ($ccaaId) {
            $answersAccumulator[] = [
                'user_id' => $userId,
                'question_id' => $ccaaQuestion->id,
                'conviviente_id' => $convivienteId,
                'answer' => (string) $ccaaId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    private function resolveProvinciaId($provinciaValue, $provinciasById, $provinciasByName): ?int
    {
        if (empty($provinciaValue)) {
            return null;
        }

        if (is_numeric($provinciaValue)) {
            return (int) $provinciaValue;
        }

        if ($provinciasByName) {
            $provincia = $provinciasByName->get($provinciaValue);
            if (! $provincia) {
                $provincia = $provinciasByName->first(function ($p) use ($provinciaValue) {
                    return stripos($p->nombre_provincia, $provinciaValue) !== false;
                });
            }

            return $provincia?->id;
        }

        return null;
    }

    private function resolveMunicipioId($municipioValue, ?int $provinciaId, $municipiosById, $municipiosByName): ?int
    {
        if (empty($municipioValue)) {
            return null;
        }

        if (is_numeric($municipioValue)) {
            return (int) $municipioValue;
        }

        if ($municipiosByName) {
            $municipio = $municipiosByName->get($municipioValue);
            if (! $municipio && $provinciaId) {
                $municipio = $municipiosByName->first(function ($m) use ($municipioValue, $provinciaId) {
                    return $m->nombre_municipio === $municipioValue && $m->provincia_id === $provinciaId;
                });
            }
            if (! $municipio) {
                $municipio = $municipiosByName->first(function ($m) use ($municipioValue) {
                    return stripos($m->nombre_municipio, $municipioValue) !== false;
                });
            }

            return $municipio?->id;
        }

        return null;
    }

    private function calculateTotalNetIncome(array $answers, array $convivientes): float
    {
        $totalNetIncome = 0;
        $userIncomesFound = 0;
        $convivienteIncomesFound = 0;

        foreach ($answers as $entry) {
            if (is_array($entry) && isset($entry['incomes']) && is_array($entry['incomes'])) {
                $netIncome = (float) ($entry['netIncome'] ?? 0);
                $totalNetIncome += $netIncome;
                $userIncomesFound++;
            }
        }

        foreach ($convivientes as $convivienteData) {
            if (isset($convivienteData['answers'])) {
                foreach ($convivienteData['answers'] as $entry) {
                    if (is_array($entry) && isset($entry['incomes']) && is_array($entry['incomes'])) {
                        $netIncome = (float) ($entry['netIncome'] ?? 0);
                        $totalNetIncome += $netIncome;
                        $convivienteIncomesFound++;
                    }
                }
            }
        }

        return $totalNetIncome;
    }
}
