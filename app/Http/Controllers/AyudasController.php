<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\Conviviente;
use App\Models\Document;
use App\Models\Organo;
use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AyudasController extends Controller
{
    public function index()
    {
        $ayudas = Ayuda::with(['requisitos.question', 'questionnaire', 'organo', 'documents.documento', 'ayudaDocumentosConvivientes.documento'])->get();
        $allQuestions = Question::all();
        $allDocuments = Document::all();
        $questionnaires = Questionnaire::all();
        $organos = Organo::all();
        $sectores = Ayuda::getSectores();

        return view('admin.ayudas', compact('ayudas', 'questionnaires', 'organos', 'sectores', 'allQuestions', 'allDocuments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_ayuda' => 'required|string|max:255',
            'sector' => 'required|string|in:'.implode(',', Ayuda::getSectores()),
            'presupuesto' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'organo_id' => 'required',
            'activo' => 'nullable|boolean',
            'cuantia_usuario' => 'nullable|numeric|min:0',
        ]);

        Ayuda::create([
            'nombre_ayuda' => $validated['nombre_ayuda'],
            'sector' => $validated['sector'],
            'presupuesto' => $validated['presupuesto'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'] ?? null,
            'organo_id' => $validated['organo_id'],
            'activo' => $request->has('activo'),
            'cuantia_usuario' => $validated['cuantia_usuario'] ?? 0,
            'questionnaire_id' => $request->input('questionnaire_id'),
            'create_time' => now(),
        ]);

        return redirect()->route('ayudas.index')->with('success', 'Ayuda creada correctamente.');
    }

    public function destroy($id)
    {
        $question = Ayuda::findOrFail($id);
        $question->delete();

        return redirect()->route('ayudas.index')->with('success', 'Ayuda eliminada correctamente.');
    }

    public function editarFechasYEstado()
    {
        $ayudas = Ayuda::select(
            'id',
            'nombre_ayuda',
            'fecha_inicio',
            'fecha_fin',
            'fecha_inicio_periodo',
            'fecha_fin_periodo',
            'cuantia_usuario',
            'presupuesto',
            'activo'
        )
            ->orderBy('nombre_ayuda')
            ->get()
            ->map(function ($ayuda) {
                $ayuda->fecha_inicio = $ayuda->fecha_inicio ? Carbon::parse($ayuda->fecha_inicio) : null;
                $ayuda->fecha_fin = $ayuda->fecha_fin ? Carbon::parse($ayuda->fecha_fin) : null;
                $ayuda->fecha_inicio_periodo = $ayuda->fecha_inicio_periodo ? Carbon::parse($ayuda->fecha_inicio_periodo) : null;
                $ayuda->fecha_fin_periodo = $ayuda->fecha_fin_periodo ? Carbon::parse($ayuda->fecha_fin_periodo) : null;

                return $ayuda;
            });

        return view('admin.ayudas-mod', compact('ayudas'));
    }

    public function updateFechasYEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'fecha_inicio_periodo' => 'nullable|date',
            'fecha_fin_periodo' => 'nullable|date|after_or_equal:fecha_inicio_periodo',
            'activo' => 'required|boolean',
            'presupuesto' => 'nullable|numeric|min:0',
            'cuantia_usuario' => 'nullable|numeric|min:0',
        ]);

        $ayuda = Ayuda::findOrFail($id);

        $ayuda->update([
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'fecha_inicio_periodo' => $validated['fecha_inicio_periodo'],
            'fecha_fin_periodo' => $validated['fecha_fin_periodo'],
            'activo' => $validated['activo'],
            'presupuesto' => $validated['presupuesto'],
            'cuantia_usuario' => $validated['cuantia_usuario'],
            'updated_at' => now(),
        ]);

        return redirect()->route('ayudas.editar')->with('success', 'Ayuda actualizada correctamente.');
    }

    public function slugExists($slug)
    {
        return response()->json(Ayuda::where('slug', $slug)->exists() ? true : false);
    }

    public function hasPrerequisites($ayudaId)
    {
        try {
            $ayuda = Ayuda::with('preRequisitos')->findOrFail($ayudaId);
            $hasPrerequisites = $ayuda->preRequisitos && $ayuda->preRequisitos->isNotEmpty();

            return response()->json([
                'success' => true,
                'hasPrerequisites' => $hasPrerequisites,
                'count' => $hasPrerequisites ? $ayuda->preRequisitos->count() : 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'hasPrerequisites' => false,
                'message' => 'Error al verificar pre-requisitos: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getMissingAnswer(Request $request, $ayudaId, $questionId)
    {
        try {
            $question = Question::findOrFail($questionId);
            $user = Auth::user();

            $targetType = $request->get('target_type', 'solicitante');
            $convivienteType = $request->get('conviviente_type', null);

            $tempPreReq = (object) [
                'id' => 0,
                'target_type' => $targetType,
                'conviviente_type' => $convivienteType,
            ];

            $targetInfo = $this->getTargetDisplayInfo($tempPreReq, $user);

            return response()->json([
                'success' => true,
                'question' => [
                    'id' => $question->id,
                    'text' => $question->text,
                    'type' => $question->type,
                    'options' => $question->options,
                    'required' => $question->required ?? false,
                ],
                'target_info' => $targetInfo,
                'ayuda_id' => $ayudaId,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la pregunta: '.$e->getMessage(),
            ], 500);
        }
    }

    public function verifyPrerequisites(Request $request, $ayudaId)
    {
        try {
            $ayuda = Ayuda::with(['preRequisitos.question', 'preRequisitos.groupRules.question'])->findOrFail($ayudaId);
            $user = Auth::user();

            if (! $ayuda->preRequisitos || $ayuda->preRequisitos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'userMeetsRequirements' => true,
                    'preRequisitos' => [],
                    'message' => 'Esta ayuda no tiene pre-requisitos',
                ]);
            }

            $preRequisitos = [];
            $allRequirementsMet = true;

            foreach ($ayuda->preRequisitos as $preReq) {
                $userMeets = $this->checkPreRequisito($preReq, $user);
                $userAnswer = $this->getUserAnswer($preReq, $user);
                if ($userMeets !== true) {
                    $allRequirementsMet = false;
                }

                $preRequisitos[] = [
                    'id' => $preReq->id,
                    'name' => $preReq->name,
                    'error_message' => $preReq->error_message,
                    'description' => $preReq->description,
                    'target_type' => $preReq->target_type,
                    'conviviente_type' => $preReq->conviviente_type,
                    'question_id' => $preReq->question_id,
                    'fallback_question_id' => $this->determineFallbackQuestionId($preReq),
                    'question_text' => $preReq->question ? $preReq->question->text : null,
                    'operator' => $preReq->operator,
                    'value' => $preReq->value,
                    'value2' => $preReq->value2,
                    'value_type' => $preReq->value_type,
                    'age_unit' => $preReq->age_unit,
                    'userMeets' => $userMeets,
                    'userAnswer' => $userAnswer,
                    'target_info' => $this->getTargetDisplayInfo($preReq, $user),
                ];
            }

            return response()->json([
                'success' => true,
                'userMeetsRequirements' => $allRequirementsMet,
                'preRequisitos' => $preRequisitos,
                'message' => $allRequirementsMet ? 'Cumples todos los pre-requisitos' : 'No cumples algunos pre-requisitos',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar pre-requisitos: '.$e->getMessage(),
            ], 500);
        }
    }

    private function checkPreRequisito($preReq, $user)
    {
        if (isset($preReq->type) && $preReq->type === 'group') {
            return $this->evaluateGroupRules($preReq->groupRules ?? [], $user, $preReq->group_logic ?? 'AND');
        }
        switch ($preReq->target_type) {
            case 'solicitante':
                return $this->checkSolicitantePreRequisito($preReq, $user);
            case 'conviviente':
                return $this->checkConvivientePreRequisito($preReq, $user);
            case 'unidad_convivencia_completa':
            case 'unidad_convivencia_sin_solicitante':
            case 'unidad_familiar_completa':
            case 'unidad_familiar_sin_solicitante':
            case 'any_conviviente':
            case 'any_familiar':
            case 'any_persona_unidad':
                return $this->checkUnidadPreRequisito($preReq, $user);
            default:
                return false;
        }
    }

    private function evaluateGroupRules($rules, $user, $logic = 'AND')
    {
        if (empty($rules)) {
            return true;
        }

        $sawNull = false;

        foreach ($rules as $rule) {
            $tempPreReq = (object) [
                'target_type' => $rule->target_type ?? 'solicitante',
                'conviviente_type' => $rule->conviviente_type ?? null,
                'question_id' => $rule->question_id ?? null,
                'operator' => $rule->operator ?? null,
                'value' => $rule->value ?? null,
                'value2' => $rule->value2 ?? null,
                'value_type' => $rule->value_type ?? 'exact',
                'age_unit' => $rule->age_unit ?? 'years',
            ];

            $result = $this->evaluateRule($tempPreReq, $user);

            if ($logic === 'OR') {
                if ($result === true) {
                    return true;
                }
                if ($result === null) {
                    $sawNull = true;
                }
            } else {
                if ($result === false) {
                    return false;
                }
                if ($result === null) {
                    $sawNull = true;
                }
            }
        }

        if ($logic === 'OR') {
            return $sawNull ? null : false;
        }

        return $sawNull ? null : true;
    }

    private function evaluateRule($preReq, $user)
    {
        switch ($preReq->target_type) {
            case 'solicitante':
                return $this->checkSolicitantePreRequisito($preReq, $user);
            case 'conviviente':
                return $this->checkConvivientePreRequisito($preReq, $user);
            case 'unidad_convivencia_completa':
            case 'unidad_convivencia_sin_solicitante':
            case 'unidad_familiar_completa':
            case 'unidad_familiar_sin_solicitante':
            case 'any_conviviente':
            case 'any_familiar':
            case 'any_persona_unidad':
                return $this->checkUnidadPreRequisito($preReq, $user);
            default:
                return false;
        }
    }

    private function determineFallbackQuestionId($preReq)
    {
        if (! empty($preReq->question_id)) {
            return $preReq->question_id;
        }
        if (isset($preReq->type) && $preReq->type === 'group') {
            $rules = method_exists($preReq, 'groupRules') ? $preReq->groupRules : ($preReq->groupRules ?? []);
            if ($rules instanceof \Illuminate\Support\Collection) {
                $rule = $rules->first(function ($r) {
                    return ! empty($r->question_id);
                });
                if ($rule) {
                    return $rule->question_id;
                }
            } elseif (is_array($rules)) {
                foreach ($rules as $r) {
                    if (! empty($r['question_id'])) {
                        return $r['question_id'];
                    }
                }
            }
        }
        if (in_array($preReq->value_type, ['age_minimum', 'age_maximum', 'age_range'])) {
            $birthDate = Question::where('slug', 'like', '%fecha_nacimiento%')
                ->orWhere('slug', 'like', '%fecha_de_nacimiento%')
                ->orWhere(function ($q) {
                    $q->where('text', 'like', '%fecha%')
                        ->where('text', 'like', '%nacimiento%');
                })
                ->orderBy('id')
                ->first();

            return $birthDate ? $birthDate->id : null;
        }

        return null;
    }

    private function checkSolicitantePreRequisito($preReq, $user)
    {
        $answer = Answer::where('user_id', $user->id)
            ->where('question_id', $preReq->question_id)
            ->first();

        if (! $answer) {
            return false;
        }

        return $this->evaluateAnswer($answer, $preReq);
    }

    private function checkConvivientePreRequisito($preReq, $user)
    {
        $convivientes = Conviviente::where('user_id', $user->id)
            ->where('tipo', $this->getConvivienteTypeFromPreReq($preReq))
            ->get();

        if ($convivientes->isEmpty()) {
            return false;
        }

        foreach ($convivientes as $conviviente) {
            $answer = Answer::where('conviviente_id', $conviviente->id)
                ->where('question_id', $preReq->question_id)
                ->first();

            if ($answer && $this->evaluateAnswer($answer, $preReq)) {
                return true;
            }
        }

        return false;
    }

    private function checkUnidadPreRequisito($preReq, $user)
    {
        $convivientes = $this->getConvivientesForUnidadType($preReq->target_type, $user);

        if ($convivientes->isEmpty()) {
            $userAnswer = Answer::where('user_id', $user->id)
                ->where('question_id', $preReq->question_id)
                ->first();
            if ($userAnswer) {
                return $this->evaluateAnswer($userAnswer, $preReq);
            }

            return null;
        }

        foreach ($convivientes as $conviviente) {
            $answer = Answer::where('conviviente_id', $conviviente->id)
                ->where('question_id', $preReq->question_id)
                ->first();

            if ($answer && $this->evaluateAnswer($answer, $preReq)) {
                return true;
            }
        }

        $userAnswer = Answer::where('user_id', $user->id)
            ->where('question_id', $preReq->question_id)
            ->first();
        if ($userAnswer) {
            return $this->evaluateAnswer($userAnswer, $preReq);
        }

        return null;
    }

    private function getUserAnswer($preReq, $user)
    {
        if (isset($preReq->type) && $preReq->type === 'group') {
            return null;
        }

        if ($preReq->target_type === 'solicitante') {
            $answer = Answer::where('user_id', $user->id)
                ->where('question_id', $preReq->question_id)
                ->first();
        } else {
            $convivientes = $this->getConvivientesForPreReq($preReq, $user);
            $answer = null;

            foreach ($convivientes as $conviviente) {
                $answer = Answer::where('conviviente_id', $conviviente->id)
                    ->where('question_id', $preReq->question_id)
                    ->first();
                if ($answer) {
                    break;
                }
            }
            if (! $answer) {
                $answer = Answer::where('user_id', $user->id)
                    ->where('question_id', $preReq->question_id)
                    ->first();
            }
        }

        return $answer ? $answer->getFormattedAnswer() : null;
    }

    private function getFallbackQuestionIdForPreReq($preReq)
    {
        if (! empty($preReq->question_id)) {
            return $preReq->question_id;
        }

        if (isset($preReq->type) && $preReq->type === 'group') {
            $rules = $preReq->groupRules ?? [];

            if ($rules instanceof \Illuminate\Support\Collection) {
                $rule = $rules->first(function ($r) {
                    return ! empty($r->question_id);
                });
                if ($rule) {
                    return $rule->question_id;
                }
            } elseif (is_array($rules)) {
                foreach ($rules as $r) {
                    if (! empty($r['question_id'])) {
                        return $r['question_id'];
                    }
                }
            }
        }

        if (in_array($preReq->value_type, ['age_minimum', 'age_maximum', 'age_range'])) {
            $birthDate = Question::where('slug', 'like', '%fecha_nacimiento%')
                ->orWhere('slug', 'like', '%fecha_de_nacimiento%')
                ->orWhere(function ($q) {
                    $q->where('text', 'like', '%fecha%')
                        ->where('text', 'like', '%nacimiento%');
                })
                ->orderBy('id')
                ->first();

            return $birthDate ? $birthDate->id : null;
        }

        return null;
    }

    private function getConvivienteTypeFromPreReq($preReq)
    {
        $typeMap = [
            'conyuge' => 'Cónyuge',
            'hijo' => 'Hijo/a',
            'padre' => 'Padre/Madre',
            'otro' => 'Otro familiar',
            'no_familiar' => 'No familiar',
        ];

        $mappedType = $typeMap[$preReq->conviviente_type] ?? $preReq->conviviente_type;

        return $mappedType;
    }

    private function getConvivientesForUnidadType($targetType, $user)
    {
        $convivientes = Conviviente::where('user_id', $user->id);

        switch ($targetType) {
            case 'unidad_convivencia_completa':
                return $convivientes->get();

            case 'unidad_convivencia_sin_solicitante':
                return $convivientes->get();

            case 'unidad_familiar_completa':
                return $convivientes->whereNotIn('tipo', ['No familiar'])->get();

            case 'unidad_familiar_sin_solicitante':
                return $convivientes->whereNotIn('tipo', ['No familiar'])->get();

            case 'any_conviviente':
                return $convivientes->get();

            case 'any_familiar':
                return $convivientes->whereNotIn('tipo', ['No familiar'])->get();

            case 'any_persona_unidad':
                return $convivientes->get();

            default:
                return collect();
        }
    }

    private function evaluateAnswer($answer, $preReq)
    {
        $answerValue = $answer->answer;
        $expectedValue = $preReq->value;

        if ($preReq->value_type === 'age_minimum' || $preReq->value_type === 'age_maximum' || $preReq->value_type === 'age_range') {
            return $this->evaluateAgeAnswer($answer, $preReq);
        }

        switch ($preReq->operator) {
            case '==':
                return $answerValue == $expectedValue;
            case '!=':
                return $answerValue != $expectedValue;
            case '>':
                return $answerValue > $expectedValue;
            case '>=':
                return $answerValue >= $expectedValue;
            case '<':
                return $answerValue < $expectedValue;
            case '<=':
                return $answerValue <= $expectedValue;
            case 'contains':
                return strpos($answerValue, $expectedValue) !== false;
            case 'not_contains':
                return strpos($answerValue, $expectedValue) === false;
            case 'between':
                return $answerValue >= $expectedValue && $answerValue <= $preReq->value2;
            case 'exists':
                return ! empty($answerValue);
            case 'not_exists':
                return empty($answerValue);
            default:
                return false;
        }
    }

    private function evaluateAgeAnswer($answer, $preReq)
    {
        if ($answer->question_id == 40) {
            $fechaNacimiento = $answer->answer;
        } else {
            $fechaNacimiento = $this->getFechaNacimientoFromAnswer($answer);
        }

        if (! $fechaNacimiento) {
            return false;
        }

        $edad = $this->calculateAge($fechaNacimiento, $preReq->age_unit);
        $expectedAge = (int) $preReq->value;

        switch ($preReq->value_type) {
            case 'age_minimum':
                return $edad >= $expectedAge;
            case 'age_maximum':
                return $edad <= $expectedAge;
            case 'age_range':
                return $edad >= $expectedAge && $edad <= (int) $preReq->value2;
            default:
                return false;
        }
    }

    private function getFechaNacimientoFromAnswer($answer)
    {
        $fechaNacimientoQuestion = \App\Models\Question::where('slug', 'fecha_nacimiento')
            ->orWhere('slug', 'fecha_de_nacimiento')
            ->orWhere('text', 'like', '%fecha%nacimiento%')
            ->first();

        if (! $fechaNacimientoQuestion) {
            return null;
        }

        $fechaAnswer = Answer::where('conviviente_id', $answer->conviviente_id)
            ->where('question_id', $fechaNacimientoQuestion->id)
            ->first();

        return $fechaAnswer ? $fechaAnswer->answer : null;
    }

    private function calculateAge($fechaNacimiento, $unit)
    {
        $fechaNac = Carbon::parse($fechaNacimiento);
        $now = Carbon::now();
        if ($fechaNac->isFuture()) {
            return 0;
        }

        switch ($unit) {
            case 'years':
                return $fechaNac->diffInYears($now);
            case 'months':
                return $fechaNac->diffInMonths($now);
            case 'days':
                return $fechaNac->diffInDays($now);
            default:
                return $fechaNac->diffInYears($now);
        }
    }

    private function getConvivientesForPreReq($preReq, $user)
    {

        if ($preReq->target_type === 'conviviente') {
            $convivienteType = $this->getConvivienteTypeFromPreReq($preReq);

            $convivientes = Conviviente::where('user_id', $user->id)
                ->where('tipo', $convivienteType)
                ->get();

            return $convivientes;
        } else {
            return $this->getConvivientesForUnidadType($preReq->target_type, $user);
        }
    }

    private function getConvivientesForTargetType($targetType, $convivienteType, $user)
    {
        if ($targetType === 'conviviente') {
            return Conviviente::where('user_id', $user->id)
                ->where('tipo', $this->getConvivienteTypeFromString($convivienteType))
                ->get();
        } else {
            return $this->getConvivientesForUnidadType($targetType, $user);
        }
    }

    private function getConvivienteTypeFromString($convivienteType)
    {
        $typeMap = [
            'conyuge' => 'Cónyuge',
            'hijo' => 'Hijo/a',
            'padre' => 'Padre/Madre',
            'otro' => 'Otro familiar',
            'no_familiar' => 'No familiar',
        ];

        return $typeMap[$convivienteType] ?? $convivienteType;
    }

    private function getConvivienteDisplayInfo($conviviente)
    {
        $nombreQuestion = Question::where('slug', 'nombre')
            ->orWhere('slug', 'solo_nombre')
            ->orWhere('slug', 'nombre_completo')
            ->first();

        $nombre = null;
        if ($nombreQuestion) {
            $allAnswers = Answer::where('conviviente_id', $conviviente->id)->get();
            $nombreAnswer = Answer::where('conviviente_id', $conviviente->id)
                ->where('question_id', $nombreQuestion->id)
                ->first();
            $nombre = $nombreAnswer ? $nombreAnswer->answer : null;
        }

        if (! $nombre || empty($nombre)) {
            $nombre = $conviviente->tipo.' '.$conviviente->index;
        }

        return [
            'id' => $conviviente->id,
            'tipo' => $conviviente->tipo,
            'index' => $conviviente->index,
            'nombre' => $nombre,
        ];
    }

    public function saveAnswer(Request $request, $ayudaId)
    {
        try {
            $user = Auth::user();
            $questionId = $request->input('question_id');
            $answer = $request->input('answer');
            $targetType = $request->input('target_type');
            $convivienteType = $request->input('conviviente_type');

            $question = Question::find($questionId);
            if (! $question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pregunta no encontrada',
                ], 404);
            }

            $convivienteId = null;
            if ($targetType === 'conviviente' && $convivienteType) {
                $convivientes = $this->getConvivientesForTargetType($targetType, $convivienteType, $user);
                if ($convivientes->isNotEmpty()) {
                    $convivienteId = $convivientes->first()->id;
                }
            }

            $processedAnswer = $this->processAnswer($answer, $question);

            Answer::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'question_id' => $questionId,
                    'conviviente_id' => $convivienteId,
                ],
                [
                    'answer' => $processedAnswer,
                ]
            );

            $ayuda = Ayuda::with('preRequisitos')->find($ayudaId);
            $allRequirementsMet = true;
            $missingAnswers = [];
            $unmetRequirements = [];

            foreach ($ayuda->preRequisitos as $preReq) {
                $userMeets = $this->checkPreRequisito($preReq, $user);
                $userAnswer = $this->getUserAnswer($preReq, $user);

                if ($userMeets === true) {
                } else {
                    $allRequirementsMet = false;
                    if ($userAnswer === null) {
                        $missingAnswers[] = [
                            'id' => $preReq->id,
                            'name' => $preReq->name,
                            'error_message' => $preReq->error_message,
                            'target_type' => $preReq->target_type,
                            'conviviente_type' => $preReq->conviviente_type,
                            'question_id' => $preReq->question_id,
                            'fallback_question_id' => $this->getFallbackQuestionIdForPreReq($preReq),
                        ];
                    } else {
                        $unmetRequirements[] = [
                            'name' => $preReq->name,
                            'error_message' => $preReq->error_message ?: $preReq->name,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'allRequirementsMet' => $allRequirementsMet,
                'missingAnswers' => $missingAnswers,
                'unmetRequirements' => $unmetRequirements,
                'message' => 'Respuesta guardada correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la respuesta',
            ], 500);
        }
    }

    private function processAnswer($answer, $question)
    {
        switch ($question->type) {
            case 'boolean':
                return ($answer === 'Sí' || $answer === '1' || $answer === 'true') ? '1' : '0';
            case 'select':
                return $answer;
            case 'multiple':
                if (is_array($answer)) {
                    return implode(',', $answer);
                }

                return $answer;
            case 'date':
                return $answer;
            case 'integer':
                return (string) intval($answer);
            default:
                return $answer;
        }
    }

    private function getTargetDisplayInfo($preReq, $user)
    {
        switch ($preReq->target_type) {
            case 'solicitante':
                return [
                    'type' => 'solicitante',
                    'display_name' => 'Tú',
                    'description' => 'Necesitamos información sobre ti',
                ];

            case 'conviviente':
                $convivientes = $this->getConvivientesForPreReq($preReq, $user);

                if ($convivientes->isNotEmpty()) {
                    $conviviente = $convivientes->first();
                    $convivienteInfo = $this->getConvivienteDisplayInfo($conviviente);

                    return [
                        'type' => 'conviviente',
                        'conviviente_type' => $preReq->conviviente_type,
                        'display_name' => $convivienteInfo['nombre'],
                        'description' => "Necesitamos información sobre {$convivienteInfo['nombre']}",
                        'conviviente_info' => $convivienteInfo,
                    ];
                } else {
                    $fallbackName = $this->getConvivienteTypeFromPreReq($preReq);

                    return [
                        'type' => 'conviviente',
                        'conviviente_type' => $preReq->conviviente_type,
                        'display_name' => $fallbackName,
                        'description' => "Necesitamos información sobre tu {$fallbackName}",
                    ];
                }

            case 'unidad_convivencia_completa':
                return [
                    'type' => 'unidad_convivencia_completa',
                    'display_name' => 'Tu hogar',
                    'description' => 'Necesitamos información sobre tu hogar (incluyéndote a ti)',
                ];

            case 'unidad_convivencia_sin_solicitante':
                return [
                    'type' => 'unidad_convivencia_sin_solicitante',
                    'display_name' => 'Tu hogar',
                    'description' => 'Necesitamos información sobre tu hogar (sin incluirte a ti)',
                ];

            case 'unidad_familiar_completa':
                return [
                    'type' => 'unidad_familiar_completa',
                    'display_name' => 'Tu familia',
                    'description' => 'Necesitamos información sobre tu familia (incluyéndote a ti)',
                ];

            case 'unidad_familiar_sin_solicitante':
                return [
                    'type' => 'unidad_familiar_sin_solicitante',
                    'display_name' => 'Tu familia',
                    'description' => 'Necesitamos información sobre tu familia (sin incluirte a ti)',
                ];

            case 'any_conviviente':
                return [
                    'type' => 'any_conviviente',
                    'display_name' => 'Alguien de tu hogar',
                    'description' => 'Necesitamos información sobre alguien de tu hogar',
                ];

            case 'any_familiar':
                return [
                    'type' => 'any_familiar',
                    'display_name' => 'Alguien de tu familia',
                    'description' => 'Necesitamos información sobre alguien de tu familia',
                ];

            case 'any_persona_unidad':
                return [
                    'type' => 'any_persona_unidad',
                    'display_name' => 'Alguien de tu hogar',
                    'description' => 'Necesitamos información sobre alguien de tu hogar',
                ];

            default:
                return [
                    'type' => $preReq->target_type,
                    'display_name' => 'Información requerida',
                    'description' => 'Necesitamos información adicional para continuar',
                ];
        }
    }
}
