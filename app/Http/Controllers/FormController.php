<?php

namespace App\Http\Controllers;

use App\Enums\QuestionnaireTipo;
use App\Events\EventUserIsBeneficiary;
use App\Events\EventUserIsNotBeneficiary;
use App\Helpers\SimulationHelper;
use App\Mail\UserNoBeneficiarioMail;
use App\Models\Ayuda;
use App\Models\MailTracking;
use App\Models\QuestionnaireDraft;
use App\Models\User;
use App\Services\AnswerService;
use App\Services\AyudaService;
use App\Services\CrmService;
use App\Services\EvaluadorAyudaService;
use App\Services\FormProcessingService;
use App\Services\QuestionnaireService;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormController extends Controller
{
    public function __construct(
        private QuestionnaireService $questionnaireService,
        private QuestionService $questionService,
        private AnswerService $answerService,
        private FormProcessingService $formProcessingService,
        private AyudaService $ayudaService,
        private CrmService $crmService,
        private EvaluadorAyudaService $evaluadorAyudaService
    ) {}

    public function show($id)
    {
        $userId = Auth::id();

        // Obtener cuestionario con ayuda
        $questionnaire = $this->questionnaireService->getQuestionnaireById($id, ['ayuda']);
        $ayuda = $questionnaire->ayuda;

        // Obtener IDs de preguntas y preguntas del cuestionario
        $questionIds = $this->questionService->getQuestionIdsByQuestionnaire($id);
        $questions = $this->questionService->getQuestionsByQuestionnaire($id);

        // Obtener respuestas del usuario
        $answers = $this->answerService->getAnswersByUserAndQuestionnaire($userId, $id);
        Log::info('Respuestas obtenidas: '.json_encode($answers->toArray()));
        Log::info('IDs de preguntas obtenidos:', $questionIds->toArray());

        // Sincronizar respuesta especial: pregunta 26 → pregunta 2
        if ($questionIds->contains(2) && (! isset($answers[2]) || is_null($answers[2]))) {
            Log::info('La pregunta 2 esta y tiene respuesta nula.');
            $this->answerService->syncAnswer26To2($userId);
            // Recargar respuestas después de la sincronización
            $answers = $this->answerService->getAnswersByUserAndQuestionnaire($userId, $id);
        }

        // Obtener condiciones y regex
        $conditions = $this->questionnaireService->getQuestionConditions($id, $questionIds->toArray());
        $regex = $this->questionService->getRegexForQuestions($questionIds->toArray());
        Log::info('Regex obtenidos:', $regex->toArray());

        // Obtener respuestas previas para todas las preguntas
        $previousAnswers = $this->answerService->getAnswersByUserAndQuestionIds($userId, $questionIds->toArray());

        // Preparar preguntas para la vista
        $mappedQuestions = $this->formProcessingService->prepareQuestionsForView($questions, $previousAnswers, $regex, (int) $id);

        // Preparar datos para la vista
        $answersArray = $answers->isEmpty() ? [] : $answers;
        $conditionsArray = $conditions->isEmpty() ? [] : $conditions;
        $isCollectorQuestionnaire = $this->questionnaireService->isCollectorQuestionnaire($id);

        return view('user.form-specific', [
            'ayuda' => $ayuda !== null ? $ayuda : $questionnaire,
            'questions' => $mappedQuestions,
            'questionnaire' => $questionnaire,
            'answers' => $answersArray,
            'conditions' => $conditionsArray,
            'isCollectorQuestionnaire' => $isCollectorQuestionnaire,
        ]);
    }

    public function store(Request $request)
    {
        $questionnaireId = $request->input('questionnaire_id');
        $userId = SimulationHelper::getCurrentUserId();

        // Obtener cuestionario y ayuda
        $questionnaire = $questionnaireId ? $this->questionnaireService->getQuestionnaireById($questionnaireId, ['ayuda']) : null;
        $ayuda = $questionnaire?->ayuda;
        $ayudaId = $ayuda?->id;

        // Si no hay ayuda_id pero sí questionnaire_id, intentar obtenerlo
        if (! $ayudaId && $questionnaireId) {
            $ayuda = $this->ayudaService->getAyudaByQuestionnaireId($questionnaireId);
            $ayudaId = $ayuda?->id;
        }

        // Prevención de error por duplicidad SOLO si hay ayuda
        if ($ayuda && $this->ayudaService->checkIfUserHasAyudaSolicitada($userId, $ayudaId)) {
            return redirect()->route('user.home')
                ->with('error', 'Ya tienes una contratacion solicitada para esta ayuda.')
                ->cookie('ayuda_duplicada', '1', 1);
        }

        // Validar respuestas
        $answers = $request->input('answers');
        if (! is_array($answers)) {
            return redirect()->route('user.home')->with('error', 'No se han recibido respuestas válidas.');
        }

        // Procesar y guardar respuestas
        $this->formProcessingService->processAndSaveAnswers($answers, $userId);

        // Manejar según tipo de cuestionario
        if ($questionnaire && $questionnaire->tipo === QuestionnaireTipo::PRE) {
            if (! $ayuda || $ayudaId === null) {
                return redirect()->route('user.home')
                    ->with('error', 'No se pudo asociar este cuestionario a una ayuda. Por favor, inténtalo de nuevo o contacta con soporte.');
            }

            return $this->handlePreQuestionnaire($ayuda, $ayudaId, $userId, $answers);
        } elseif ($questionnaire && $questionnaire->tipo === QuestionnaireTipo::COLLECTOR) {
            return $this->handleCollectorQuestionnaire($userId, $answers);
        }

        return redirect()->route('user.home');
    }

    /**
     * Maneja el procesamiento de cuestionario tipo PRE
     */
    private function handlePreQuestionnaire($ayuda, ?int $ayudaId, int $userId, array $answers)
    {
        // Evaluar si es beneficiario
        $evaluacion = $this->evaluadorAyudaService->evaluarJson($ayudaId, $userId);

        // Crear ayuda solicitada
        // Si no hay lógica de elegibilidad, considerar como pendiente de revisión manual
        $razonesNoCumple = $evaluacion['razones_no_cumple'] ?? [];
        $tieneLogicaElegibilidad = ! empty($evaluacion['detalles']);

        $this->ayudaService->createAyudaSolicitada([
            'user_id' => $userId,
            'ayuda_id' => $ayudaId,
            'estado' => $evaluacion['es_beneficiario']
                ? 'Pendiente de tramitar'
                : ($tieneLogicaElegibilidad ? 'Rechazado' : 'Pendiente de revisión'),
            'fecha_solicitud' => now(),
            'observaciones' => json_encode($answers),
            'motivo_rechazo' => json_encode($razonesNoCumple),
        ]);

        // Actualizar CRM y disparar eventos según resultado (solo si hay lógica de elegibilidad)
        if ($tieneLogicaElegibilidad && $ayuda) {
            $user = User::find($userId);
            if ($user) {
                if ($evaluacion['es_beneficiario']) {
                    $this->crmService->markUserAsBeneficiary($userId, $ayudaId);
                    event(new EventUserIsBeneficiary($user, $ayuda, ['hubspot']));
                } else {
                    $this->crmService->markUserAsNonBeneficiary($userId, $ayudaId);
                    event(new EventUserIsNotBeneficiary($user, $ayuda, $razonesNoCumple, ['hubspot']));
                }
            }
        }

        // Eliminar registros obsoletos
        $this->crmService->deleteObsoleteUserAyudas($userId);

        // Redirigir según el resultado
        if ($evaluacion['es_beneficiario']) {
            return redirect()->route('user.beneficiario', ['ayuda_id' => $ayudaId]);
        } else {
            // Si no hay lógica de elegibilidad, redirigir a una página de "pendiente de revisión"
            if (! $tieneLogicaElegibilidad) {
                return redirect()->route('user.home')->with('info', 'Tu solicitud ha sido registrada y está pendiente de revisión manual.');
            }

            // Si hay lógica y no cumple, enviar correo y redirigir a no-beneficiario
            try {
                $currentUser = SimulationHelper::getCurrentUser();
                Mail::to($currentUser->email)->send(new UserNoBeneficiarioMail($currentUser, $ayuda));
                MailTracking::track($currentUser, UserNoBeneficiarioMail::class);
            } catch (\Throwable $th) {
                Log::error('Error al enviar el correo de no beneficiario:', ['error' => $th->getMessage()]);
            }
            session(['motivos' => $razonesNoCumple]);

            return redirect()->route('user.no-beneficiario', ['ayuda_id' => $ayudaId]);
        }
    }

    /**
     * Maneja el procesamiento de cuestionario tipo COLLECTOR
     */
    private function handleCollectorQuestionnaire(int $userId, array $answers)
    {
        // Procesar respuestas calculadas (edad, etc.)
        $this->formProcessingService->processCollectorCalculatedAnswers($userId, $answers);

        // Actualizar CRM
        $this->crmService->markUserAsTestCompleted($userId);

        return redirect()->route('user.onboarding');
    }

    public function storeDraft(Request $request)
    {
        if (! $request->ajax() && ! $request->header('X-Requested-With')) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {
            $validated = $request->validate([
                'questionnaire_id' => 'required|exists:questionnaires,id',
                'direction' => 'required|in:next,back',
                'time_start' => 'required|date_format:Y-m-d H:i:s',
                'time_end' => 'required|date_format:Y-m-d H:i:s',
                'respuesta' => 'required|string',
                'session_id' => 'required|string',
            ]);

            // Verificar que el cuestionario existe
            $this->questionnaireService->getQuestionnaireById($validated['questionnaire_id']);

            $draft = new QuestionnaireDraft([
                'user_id' => Auth::id(),
                'questionnaire_id' => $validated['questionnaire_id'],
                'direction' => $validated['direction'],
                'time_start' => $validated['time_start'],
                'time_end' => $validated['time_end'],
                'respuesta' => $request['respuesta'],
                'session_id' => $validated['session_id'],
                'session_token' => session('current_questionnaire_token'),
            ]);

            $draft->touch();
            $draft->save();

            return response()->json(['success' => true, 'draft_id' => $draft->id], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error guardando draft:', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
