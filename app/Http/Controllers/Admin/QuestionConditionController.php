<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ayuda;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use App\Models\QuestionnaireConditionVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionConditionController extends Controller
{
    /**
     * /Devuelve las condiciones del cuestionario principal de la ayuda, es decir, el especifico
     * que esta indicado en tabla ayudas
     * !!quizas tengamos que quitar esa columna porque cada ayuda tiene varios formularios
     *
     * @param  mixed  $ayuda_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function condicionesCuestionario($ayuda_id)
    {
        $ayuda = Ayuda::with('questionnaire')->findOrFail($ayuda_id);
        $questionnaire = $ayuda->questionnaire;
        if (! $questionnaire) {
            return response()->json(['error' => 'La ayuda no tiene cuestionario asociado'], 404);
        }
        $questions = $questionnaire->questions()->orderBy('questionnaire_questions.orden')->get();
        $conditions = QuestionCondition::where('questionnaire_id', $questionnaire->id)->get();
        $questionIds = collect($questions->pluck('id')->all());
        foreach ($conditions as $cond) {
            if ($cond->question_id && ! $questionIds->contains($cond->question_id)) {
                $questionIds->push($cond->question_id);
            }
            if ($cond->next_question_id && ! $questionIds->contains($cond->next_question_id)) {
                $questionIds->push($cond->next_question_id);
            }
        }
        $allQuestions = Question::whereIn('id', $questionIds)->get();
        $questionsArr = $allQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'text' => $q->text,
                'subtext' => $q->sub_text,
                'type' => $q->type,
                'options' => $q->options,
                'slug' => $q->slug,
            ];
        });

        return response()->json([
            'questions' => $questionsArr,
            'conditions' => $conditions,
        ]);
    }

    // Obtener todas las preguntas y condiciones de un cuestionario
    public function index($questionnaireId)
    {
        $questionnaire = Questionnaire::with('questions')->findOrFail($questionnaireId);
        $questions = $questionnaire->questions;
        $conditions = QuestionCondition::where('questionnaire_id', $questionnaireId)->get();

        return response()->json([
            'questions' => $questions,
            'conditions' => $conditions,
        ]);
    }

    // Crear pregunta
    public function storeQuestion(Request $request, $questionnaireId)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'type' => 'required|string',
            'options' => 'nullable|array',
        ]);
        $question = Question::create([
            'text' => $data['text'],
            'type' => $data['type'],
            'options' => $data['options'] ?? [],
        ]);
        $questionnaire = Questionnaire::findOrFail($questionnaireId);
        $questionnaire->questions()->attach($question->id);

        return response()->json($question);
    }

    // Editar pregunta
    public function updateQuestion(Request $request, $questionId)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'type' => 'required|string',
            'options' => 'nullable|array',
        ]);
        $question = Question::findOrFail($questionId);
        $question->update([
            'text' => $data['text'],
            'type' => $data['type'],
            'options' => $data['options'] ?? [],
        ]);

        return response()->json($question);
    }

    // Eliminar pregunta
    public function destroyQuestion($questionId)
    {
        $question = Question::findOrFail($questionId);
        $question->questionnaires()->detach();
        $question->delete();

        return response()->json(['success' => true]);
    }

    // Obtener todas las condiciones de un cuestionario
    public function indexConditions($questionnaireId)
    {
        // Prioridad: Draft actual > Versión activa > Datos actuales
        $currentDraft = QuestionnaireConditionVersion::getCurrentDraft($questionnaireId);

        if ($currentDraft) {
            // Si hay un draft, mostrar el draft (lo que está editando actualmente)
            $conditions = collect($currentDraft->conditions_data);
        } else {
            // Si no hay draft, intentar versión activa
            $activeVersion = QuestionnaireConditionVersion::getActiveVersion($questionnaireId);
            if ($activeVersion) {
                $conditions = collect($activeVersion->conditions_data);
            } else {
                // Fallback a datos actuales
                $conditions = QuestionCondition::where('questionnaire_id', $questionnaireId)->get();
            }
        }

        return response()->json($conditions);
    }

    public function createConditionsFromDraft(Request $request, $questionnaireId)
    {
        try {
            $data = $request->validate([
                'conditions' => 'required|array',
                'conditions.*.question_id' => 'required|integer',
                'conditions.*.next_question_id' => 'nullable|integer',
                'conditions.*.operator' => 'required|string',
                'conditions.*.value' => 'required',
            ]);

            QuestionCondition::where('questionnaire_id', $questionnaireId)->delete();

            foreach ($data['conditions'] as $condition) {
                // Normalizar operador
                $operator = $condition['operator'];
                if ($operator === '=') {
                    $operator = '==';
                }

                QuestionCondition::create([
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $condition['question_id'],
                    'next_question_id' => $condition['next_question_id'],
                    'operator' => $operator,
                    'value' => $condition['value'],
                    'order' => 1,
                ]);
            }

            session()->forget('conditions_draft_'.$questionnaireId);

            return response()->json([
                'success' => true,
                'message' => 'Condiciones creadas correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear las condiciones: '.$e->getMessage(),
            ], 500);
        }
    }

    // Crear condición
    public function storeCondition(Request $request, $questionnaireId)
    {
        try {
            if (! Auth::check()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            if (! Auth::user()->is_admin) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $data = $request->validate([
                'question_id' => 'required|integer',
                'operator' => 'required|string',
                'value' => 'nullable',
                'next_question_id' => 'nullable',
                'order' => 'nullable|integer',
            ]);
            $lastOrder = QuestionCondition::where('questionnaire_id', $questionnaireId)
                ->max('order') ?? 0;
            $newOrder = $lastOrder + 1;

            // Normalizar operador: convertir '=' a '=='
            $operator = $data['operator'];
            if ($operator === '=') {
                $operator = '==';
            }

            $value = $data['value'];
            if (is_string($value) && ! empty($value)) {
                if (strpos($value, ',') !== false) {
                    $value = array_map('trim', explode(',', $value));
                }
            }
            // Normalizar valores: mantener valores lógicos, no convertir índices
            // Para números, convertir solo si es realmente numérico
            if (is_numeric($value) && ! is_string($value)) {
                $value = (float) $value;
            } elseif (is_array($value)) {
                $value = array_map(function ($v) {
                    if (is_numeric($v) && ! is_string($v)) {
                        return (float) $v;
                    }

                    return $v;
                }, $value);
            }

            $nextQuestionId = $data['next_question_id'];
            if (empty($nextQuestionId) || $nextQuestionId === 'FIN') {
                $nextQuestionId = null;
            } else {
                if (! is_numeric($nextQuestionId)) {
                    return response()->json(['error' => 'next_question_id debe ser un número válido'], 422);
                }
                $nextQuestionId = (int) $nextQuestionId;
            }

            $cond = QuestionCondition::create([
                'question_id' => $data['question_id'],
                'operator' => $operator,
                'value' => $value,
                'next_question_id' => $nextQuestionId,
                'questionnaire_id' => $questionnaireId,
                'order' => $newOrder,
            ]);

            return response()->json($cond);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Editar condición
    public function updateCondition(Request $request, $conditionId)
    {
        $data = $request->validate([
            'question_id' => 'required|integer',
            'operator' => 'required|string',
            'value' => 'nullable',
            'next_question_id' => 'nullable',
            'order' => 'nullable|integer',
        ]);

        // Normalizar operador: convertir '=' a '=='
        $operator = $data['operator'];
        if ($operator === '=') {
            $operator = '==';
        }

        $value = $data['value'];
        if (is_string($value) && ! empty($value)) {
            if (strpos($value, ',') !== false) {
                $value = array_map('trim', explode(',', $value));
            }
        }
        // Normalizar valores: mantener valores lógicos, no convertir índices
        if (is_numeric($value) && ! is_string($value)) {
            $value = (float) $value;
        } elseif (is_array($value)) {
            $value = array_map(function ($v) {
                if (is_numeric($v) && ! is_string($v)) {
                    return (float) $v;
                }

                return $v;
            }, $value);
        }

        $nextQuestionId = $data['next_question_id'];
        if (empty($nextQuestionId) || $nextQuestionId === 'FIN') {
            $nextQuestionId = null;
        } else {
            if (! is_numeric($nextQuestionId)) {
                return response()->json(['error' => 'next_question_id debe ser un número válido'], 422);
            }
            $nextQuestionId = (int) $nextQuestionId;
        }

        $cond = QuestionCondition::findOrFail($conditionId);
        $cond->update([
            'question_id' => $data['question_id'],
            'operator' => $operator,
            'value' => $value,
            'next_question_id' => $nextQuestionId,
            'order' => $data['order'] ?? $cond->order,
        ]);

        return response()->json($cond);
    }

    // Eliminar condición
    public function destroyCondition($conditionId)
    {
        $cond = QuestionCondition::findOrFail($conditionId);
        $cond->delete();

        return response()->json(['success' => true]);
    }

    public function destroyAllConditions($questionnaireId)
    {
        try {
            if (! Auth::check()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            if (! Auth::user()->is_admin) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $deletedCount = QuestionCondition::where('questionnaire_id', $questionnaireId)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deletedCount} condiciones",
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeBatchConditions(Request $request, $questionnaireId)
    {
        try {
            if (! Auth::check()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            if (! Auth::user()->is_admin) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $data = $request->validate([
                'question_id' => 'required|integer',
                'conditions' => 'required|array',
                'conditions.*.operator' => 'required|string',
                'conditions.*.value' => 'nullable',
                'conditions.*.next_question_id' => 'nullable',
            ]);

            $currentDraft = QuestionnaireConditionVersion::getCurrentDraft($questionnaireId);

            if ($currentDraft) {
                $conditionsData = $currentDraft->conditions_data;

                $conditionsData = array_filter($conditionsData, function ($cond) use ($data) {
                    return $cond['question_id'] != $data['question_id'];
                });

                $lastOrder = count($conditionsData) > 0 ? max(array_column($conditionsData, 'order')) : 0;

                foreach ($data['conditions'] as $index => $conditionData) {
                    $newOrder = $lastOrder + $index + 1;

                    // Normalizar operador
                    $operator = $conditionData['operator'];
                    if ($operator === '=') {
                        $operator = '==';
                    }

                    $value = $conditionData['value'];
                    if (is_string($value) && ! empty($value)) {
                        if (strpos($value, ',') !== false) {
                            $value = array_map('trim', explode(',', $value));
                        }
                    }
                    // Normalizar valores: mantener valores lógicos
                    if (is_numeric($value) && ! is_string($value)) {
                        $value = (float) $value;
                    } elseif (is_array($value)) {
                        $value = array_map(function ($v) {
                            if (is_numeric($v) && ! is_string($v)) {
                                return (float) $v;
                            }

                            return $v;
                        }, $value);
                    }

                    $nextQuestionId = $conditionData['next_question_id'];
                    if (empty($nextQuestionId) || $nextQuestionId === 'FIN') {
                        $nextQuestionId = null;
                    } else {
                        if (! is_numeric($nextQuestionId)) {
                            return response()->json(['error' => 'next_question_id debe ser un número válido'], 422);
                        }
                        $nextQuestionId = (int) $nextQuestionId;
                    }

                    $conditionsData[] = [
                        'question_id' => $data['question_id'],
                        'operator' => $operator,
                        'value' => $value,
                        'next_question_id' => $nextQuestionId,
                        'questionnaire_id' => $questionnaireId,
                        'order' => $newOrder,
                    ];
                }

                $currentDraft->update(['conditions_data' => array_values($conditionsData)]);
                $createdConditions = array_slice($conditionsData, -count($data['conditions']));

            } else {
                $existingConditions = QuestionCondition::where('questionnaire_id', $questionnaireId)->get()->toArray();

                $existingConditions = array_filter($existingConditions, function ($cond) use ($data) {
                    return $cond['question_id'] != $data['question_id'];
                });

                $lastOrder = count($existingConditions) > 0 ? max(array_column($existingConditions, 'order')) : 0;

                foreach ($data['conditions'] as $index => $conditionData) {
                    $newOrder = $lastOrder + $index + 1;

                    // Normalizar operador
                    $operator = $conditionData['operator'];
                    if ($operator === '=') {
                        $operator = '==';
                    }

                    $value = $conditionData['value'];
                    if (is_string($value) && ! empty($value)) {
                        if (strpos($value, ',') !== false) {
                            $value = array_map('trim', explode(',', $value));
                        }
                    }
                    // Normalizar valores: mantener valores lógicos
                    if (is_numeric($value) && ! is_string($value)) {
                        $value = (float) $value;
                    } elseif (is_array($value)) {
                        $value = array_map(function ($v) {
                            if (is_numeric($v) && ! is_string($v)) {
                                return (float) $v;
                            }

                            return $v;
                        }, $value);
                    }

                    $nextQuestionId = $conditionData['next_question_id'];
                    if (empty($nextQuestionId) || $nextQuestionId === 'FIN') {
                        $nextQuestionId = null;
                    } else {
                        if (! is_numeric($nextQuestionId)) {
                            return response()->json(['error' => 'next_question_id debe ser un número válido'], 422);
                        }
                        $nextQuestionId = (int) $nextQuestionId;
                    }

                    $existingConditions[] = [
                        'question_id' => $data['question_id'],
                        'operator' => $operator,
                        'value' => $value,
                        'next_question_id' => $nextQuestionId,
                        'questionnaire_id' => $questionnaireId,
                        'order' => $newOrder,
                    ];
                }

                QuestionnaireConditionVersion::create([
                    'questionnaire_id' => $questionnaireId,
                    'version_number' => QuestionnaireConditionVersion::getNextVersionNumber($questionnaireId),
                    'conditions_data' => array_values($existingConditions),
                    'is_active' => false,
                    'is_draft' => true,
                    'created_by' => Auth::user()->id ?? 1,
                    'version_description' => 'Draft creado automáticamente desde el editor',
                ]);

                $createdConditions = array_slice($existingConditions, -count($data['conditions']));
            }

            return response()->json([
                'message' => 'Condiciones actualizadas correctamente',
                'conditions' => $createdConditions,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
