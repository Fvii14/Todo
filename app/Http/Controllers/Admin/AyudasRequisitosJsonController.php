<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ayuda;
use App\Models\AyudaRequisitoJson;
use App\Models\AyudaRequisitoVersion;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use App\Models\QuestionnaireConditionVersion;
use App\Services\EvaluadorAyudaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AyudasRequisitosJsonController extends Controller
{
    public function index()
    {
        $ayudas = Ayuda::all();

        return view('admin.logicas', compact('ayudas'));
    }

    public function ayuda($id)
    {
        $ayuda = Ayuda::find($id);

        // Prioridad: Draft actual > Versión activa > Datos actuales
        $currentDraft = AyudaRequisitoVersion::getCurrentDraft($id);

        if ($currentDraft) {
            // Si hay un draft, mostrar el draft (lo que está editando actualmente)
            $ayudaRequisitos = collect([$currentDraft]);
        } else {
            // Si no hay draft, intentar versión activa
            $activeVersion = AyudaRequisitoVersion::getActiveVersion($id);
            if ($activeVersion) {
                $ayudaRequisitos = collect([$activeVersion]);
            } else {
                // Fallback a datos actuales
                $ayudaRequisitos = AyudaRequisitoJson::where('ayuda_id', $id)->get();
            }
        }

        $questionIds = collect();
        $requisitosPlanos = [];
        foreach ($ayudaRequisitos as $req) {
            // Puede ser array de objetos o string
            $bloques = is_array($req->json_regla) ? $req->json_regla : [
                [
                    'descripcion' => $req->descripcion,
                    'json_regla' => $req->json_regla,
                ],
            ];
            foreach ($bloques as $bloque) {
                $regla = $bloque['json_regla'] ?? null;
                if (is_string($regla)) {
                    $decoded = json_decode($regla, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $decoded = json_decode(stripslashes($regla), true);
                    }
                    $rules = $decoded['rules'] ?? [];
                    $this->collectQuestionIds($rules, $questionIds);
                    $requisitosPlanos[] = [
                        'descripcion' => $bloque['descripcion'] ?? '',
                        'condition' => $decoded['condition'] ?? '',
                        'rules' => $rules,
                    ];
                }
            }
        }
        $questionIds = $questionIds->unique()->values();
        $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');
        // Añadir el texto de la pregunta a cada regla
        foreach ($requisitosPlanos as &$req) {
            $this->enrichRules($req['rules'], $questions);
        }
        unset($req);
        // Para compatibilidad con el frontend, lo pasamos como si fuera $ayudaRequisitos
        $allQuestions = Question::all();
        $questionsArr = $allQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'text' => $q->text,
                'type' => $q->type,
                'options' => $q->options,
            ];
        });
        $questionnaire = $ayuda->questionnaire;

        return view('admin.logicasayuda', [
            'ayuda' => $ayuda,
            'ayudaRequisitos' => $requisitosPlanos,
            'questionTexts' => $questionsArr,
            'questionnaire' => $questionnaire,
        ]);
    }

    public function storeJson(Request $request, $ayuda_id)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'json_regla' => 'required',
        ]);
        $requisito = new AyudaRequisitoJson;
        $requisito->ayuda_id = $ayuda_id;
        $requisito->descripcion = $request->input('descripcion');
        $requisito->json_regla = $request->input('json_regla');
        $requisito->save();

        return response()->json(['success' => true, 'id' => $requisito->id]);
    }

    public function updateJson(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'sometimes|required|string',
            'json_regla' => 'sometimes|nullable',
        ]);
        $requisito = AyudaRequisitoJson::findOrFail($id);
        if ($request->has('descripcion')) {
            $requisito->descripcion = $request->input('descripcion');
        }
        if ($request->has('json_regla')) {
            $requisito->json_regla = $request->input('json_regla');
        }
        $requisito->save();

        return response()->json(['success' => true]);
    }

    public function bulkUpdate(Request $request, $ayuda_id)
    {
        $requisitos = $request->input('requisitos', []);
        foreach ($requisitos as $req) {
            if (isset($req['id'])) {
                $requisito = AyudaRequisitoJson::find($req['id']);
                if ($requisito) {
                    $requisito->descripcion = $req['descripcion'] ?? $requisito->descripcion;
                    $requisito->json_regla = $req['json_regla'] ?? $requisito->json_regla;
                    $requisito->save();
                }
            } else {
                $nuevo = new AyudaRequisitoJson;
                $nuevo->ayuda_id = $ayuda_id;
                $nuevo->descripcion = $req['descripcion'] ?? '';
                $nuevo->json_regla = $req['json_regla'] ?? '';
                $nuevo->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function updateAllJson(Request $request, $ayuda_id)
    {
        try {
            $requisitos = $request->input('requisitos', []);
            AyudaRequisitoJson::where('ayuda_id', $ayuda_id)->delete();
            foreach ($requisitos as $requisito) {
                AyudaRequisitoJson::create([
                    'ayuda_id' => $ayuda_id,
                    'descripcion' => $requisito['descripcion'],
                    'json_regla' => $requisito['json_regla'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Requisitos actualizados correctamente',
                'count' => count($requisitos),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar requisitos: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $requisito = AyudaRequisitoJson::findOrFail($id);
        $requisito->delete();

        return response()->json(['success' => true]);
    }

    public function getQuestionnaireByAyuda($ayudaId)
    {
        try {
            $questionnaire = Questionnaire::where('ayuda_id', $ayudaId)->first();

            if (! $questionnaire) {
                return response()->json([
                    'message' => 'No hay cuestionario asociado a esta ayuda',
                    'questionnaire_id' => null,
                ]);
            }
            $questions = Question::join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
                ->where('questionnaire_questions.questionnaire_id', $questionnaire->id)
                ->select('questions.*', 'questionnaire_questions.orden as order')
                ->orderBy('questionnaire_questions.orden')
                ->get();
            $conditions = QuestionCondition::where('questionnaire_id', $questionnaire->id)
                ->orderBy('order')
                ->get();

            return response()->json([
                'questionnaire_id' => $questionnaire->id,
                'questionnaire_name' => $questionnaire->name ?? 'Cuestionario sin nombre',
                'questions' => $questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'text' => $q->text,
                        'type' => $q->type,
                        'options' => $q->options,
                        'slug' => $q->slug,
                        'order' => $q->order,
                    ];
                }),
                'conditions' => $conditions->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'questionnaire_id' => $c->questionnaire_id,
                        'question_id' => $c->question_id,
                        'operator' => $c->operator,
                        'value' => $c->value,
                        'next_question_id' => $c->next_question_id,
                        'order' => $c->order,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener el cuestionario: '.$e->getMessage(),
            ], 500);
        }
    }

    private function collectQuestionIds($rules, &$questionIds)
    {
        foreach ($rules as $rule) {
            if (isset($rule['condition']) && isset($rule['rules']) && is_array($rule['rules'])) {
                $this->collectQuestionIds($rule['rules'], $questionIds);
            } elseif (isset($rule['question_id'])) {
                $questionIds->push($rule['question_id']);
            }
        }
    }

    private function enrichRules(&$rules, $questions)
    {
        foreach ($rules as &$rule) {
            if (isset($rule['condition']) && isset($rule['rules']) && is_array($rule['rules'])) {
                $this->enrichRules($rule['rules'], $questions);
            } else {
                $rule['question_text'] = isset($rule['question_id']) && isset($questions[$rule['question_id']])
                    ? $questions[$rule['question_id']]->text
                    : 'Pregunta desconocida';
                if (isset($rule['question_id']) && isset($questions[$rule['question_id']])) {
                    $q = $questions[$rule['question_id']];
                    if (in_array($q->type, ['select', 'multiple']) && is_array($q->options)) {
                        if (is_array($rule['value'])) {
                            $rule['value_text'] = array_map(function ($v) use ($q) {
                                $idx = is_numeric($v) ? (int) $v : $v;

                                return $q->options[$idx] ?? $v;
                            }, $rule['value']);
                        } else {
                            $idx = is_numeric($rule['value']) ? (int) $rule['value'] : $rule['value'];
                            $rule['value_text'] = $q->options[$idx] ?? $rule['value'];
                        }
                    } elseif ($q->type === 'boolean') {
                        if ($rule['value'] === true || $rule['value'] === '1' || $rule['value'] === 1) {
                            $rule['value_text'] = 'Sí';
                        } elseif ($rule['value'] === false || $rule['value'] === '0' || $rule['value'] === 0) {
                            $rule['value_text'] = 'No';
                        } else {
                            $rule['value_text'] = $rule['value'];
                        }
                    }
                }
            }
        }
    }

    public function testRequirements(Request $request, $ayudaId)
    {
        try {
            $profile = $request->input('profile', []);
            $registro = DB::table('ayuda_requisitos_json')
                ->where('ayuda_id', $ayudaId)
                ->first();

            if (! $registro) {
                return response()->json([
                    'es_beneficiario' => false,
                    'detalles' => [],
                    'razones_no_cumple' => ['No hay requisitos definidos para esta ayuda'],
                    'condiciones_desconocidas' => [],
                ]);
            }

            $reglas = json_decode($registro->json_regla, true);
            $resultados = [];
            $cumpleGlobal = true;
            $razonesNoCumple = [];
            $condicionesDesconocidas = [];

            foreach ($reglas as $reglaItem) {
                $regla = json_decode($reglaItem['json_regla'], true);

                $resultado = $this->evaluarReglaParaPerfil($regla, $profile);

                $resultados[] = [
                    'descripcion' => $reglaItem['descripcion'] ?? 'Sin descripción',
                    'regla' => $reglaItem['json_regla'],
                    'resultado' => $resultado['cumple'] ? '✅ CUMPLE' : ($resultado['desconocida'] ? '❓ DESCONOCIDA' : '❌ NO CUMPLE'),
                    'detalles' => $resultado['detalles'] ?? [],
                ];

                if ($resultado['desconocida']) {
                    $condicionesDesconocidas[] = $reglaItem['descripcion'] ?? 'Sin descripción';
                } elseif (! $resultado['cumple']) {
                    $razonesNoCumple[] = $reglaItem['descripcion'] ?? 'Sin descripción';
                    $cumpleGlobal = false;
                }
            }

            $puedeDeterminar = empty($condicionesDesconocidas);
            $cumpleGlobal = $puedeDeterminar ? $cumpleGlobal : false;

            return response()->json([
                'es_beneficiario' => $cumpleGlobal,
                'puede_determinar' => $puedeDeterminar,
                'detalles' => $resultados,
                'razones_no_cumple' => $razonesNoCumple,
                'condiciones_desconocidas' => $condicionesDesconocidas,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'es_beneficiario' => false,
                'detalles' => [['descripcion' => 'Error', 'resultado' => 'Error al evaluar requisitos: '.$e->getMessage()]],
                'razones_no_cumple' => ['Error técnico'],
                'condiciones_desconocidas' => [],
            ], 500);
        }
    }

    private function evaluarReglaParaPerfil(array $regla, array $profile): array
    {
        $condicion = $regla['condition'] ?? 'AND';
        $rules = $regla['rules'] ?? [];

        $resultados = [];
        $hayDesconocidas = false;
        $hayCumple = false;

        foreach ($rules as $rule) {
            $resultado = $this->evaluarRuleParaPerfil($rule, $profile);
            $resultados[] = $resultado;

            if ($resultado['desconocida']) {
                $hayDesconocidas = true;
            } elseif ($resultado['cumple']) {
                $hayCumple = true;
            }
        }

        if ($condicion === 'OR') {
            if ($hayCumple) {
                return [
                    'cumple' => true,
                    'desconocida' => false,
                    'detalles' => $resultados,
                ];
            } elseif ($hayDesconocidas) {
                return [
                    'cumple' => false,
                    'desconocida' => true,
                    'detalles' => $resultados,
                ];
            } else {
                return [
                    'cumple' => false,
                    'desconocida' => false,
                    'detalles' => $resultados,
                ];
            }
        }

        if ($hayDesconocidas) {
            return [
                'cumple' => false,
                'desconocida' => true,
                'detalles' => $resultados,
            ];
        }

        $cumple = ! in_array(false, array_column($resultados, 'cumple'), true);

        return [
            'cumple' => $cumple,
            'desconocida' => false,
            'detalles' => $resultados,
        ];
    }

    private function evaluarRuleParaPerfil(array $rule, array $profile): array
    {
        $questionId = $rule['question_id'] ?? null;
        if (! $questionId) {
            return [
                'cumple' => false,
                'desconocida' => false,
                'detalles' => ['Pregunta no especificada'],
            ];
        }

        if (! isset($profile[$questionId]) || $profile[$questionId] === null || $profile[$questionId] === '') {
            return [
                'cumple' => false,
                'desconocida' => true,
                'detalles' => ["Pregunta ID $questionId no incluida en el perfil o sin respuesta"],
            ];
        }

        $answer = $profile[$questionId];
        $operator = $rule['operator'] ?? '=';
        $expectedValue = $rule['value'] ?? null;

        $cumple = $this->evaluarCondicion($answer, $operator, $expectedValue);

        return [
            'cumple' => $cumple,
            'desconocida' => false,
            'detalles' => [
                'pregunta_id' => $questionId,
                'respuesta' => $answer,
                'operador' => $operator,
                'valor_esperado' => $expectedValue,
                'resultado' => $cumple,
            ],
        ];
    }

    private function evaluarCondicion($actualValue, $operator, $expectedValue): bool
    {
        switch ($operator) {
            case '=':
            case '==':
                return $actualValue == $expectedValue;
            case '!=':
                return $actualValue != $expectedValue;
            case '>':
                return floatval($actualValue) > floatval($expectedValue);
            case '>=':
                return floatval($actualValue) >= floatval($expectedValue);
            case '<':
                return floatval($actualValue) < floatval($expectedValue);
            case '<=':
                return floatval($actualValue) <= floatval($expectedValue);
            case 'contains':
                return strpos($actualValue, $expectedValue) !== false;
            case 'in':
                return in_array($actualValue, (array) $expectedValue);
            case 'not_in':
                return ! in_array($actualValue, (array) $expectedValue);
            default:
                return false;
        }
    }

    public function testUserRequirements(Request $request, $ayudaId)
    {
        try {
            $userId = $request->input('user_id');

            $evaluador = new EvaluadorAyudaService;
            $resultado = $evaluador->evaluarParaTester($ayudaId, $userId);

            return response()->json($resultado);

        } catch (\Exception $e) {
            return response()->json([
                'es_beneficiario' => false,
                'detalles' => [['descripcion' => 'Error', 'resultado' => 'Error al probar usuario: '.$e->getMessage()]],
                'razones_no_cumple' => ['Error técnico'],
                'condiciones_desconocidas' => [],
            ], 500);
        }
    }

    public function getQuestionnaireData($ayudaId)
    {
        $ayuda = Ayuda::findOrFail($ayudaId);
        $questionnaire = $ayuda->questionnaire;

        if (! $questionnaire) {
            return response()->json([
                'questions' => [],
                'conditions' => [],
            ]);
        }

        $questions = $this->getQuestionnaireDataRaw($ayudaId);

        // Prioridad: Draft actual > Versión activa > Datos actuales
        $currentDraft = QuestionnaireConditionVersion::getCurrentDraft($questionnaire->id);

        if ($currentDraft) {
            // Si hay un draft, mostrar el draft (lo que está editando actualmente)
            $conditions = collect($currentDraft->conditions_data);
        } else {
            // Si no hay draft, intentar versión activa
            $activeVersion = QuestionnaireConditionVersion::getActiveVersion($questionnaire->id);
            if ($activeVersion) {
                $conditions = collect($activeVersion->conditions_data);
            } else {
                // Fallback a datos actuales
                $conditions = QuestionCondition::where('questionnaire_id', $questionnaire->id)->get();
            }
        }

        return response()->json([
            'questions' => $questions,
            'conditions' => $conditions,
        ]);
    }

    private function getQuestionnaireDataRaw($ayudaId)
    {
        $questionnaire = Questionnaire::where('ayuda_id', $ayudaId)->first();

        if (! $questionnaire) {
            return collect([]);
        }

        return Question::join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->where('questionnaire_questions.questionnaire_id', $questionnaire->id)
            ->select('questions.*', 'questionnaire_questions.orden as order')
            ->orderBy('questionnaire_questions.orden')
            ->get();
    }

    public function validateFlow(Request $request, $ayudaId)
    {
        $ayuda = Ayuda::findOrFail($ayudaId);
        $questionnaire = $ayuda->questionnaire;

        if (! $questionnaire) {
            return response()->json([
                'isValid' => false,
                'summary' => 'No hay cuestionario asociado',
                'description' => 'Esta ayuda no tiene un cuestionario configurado',
                'details' => [[
                    'type' => 'error',
                    'title' => 'Cuestionario faltante',
                    'message' => 'No se encontró un cuestionario asociado a esta ayuda',
                ]],
            ]);
        }

        $questions = $this->getQuestionnaireDataRaw($ayudaId);
        $conditions = QuestionCondition::where('questionnaire_id', $questionnaire->id)->get();

        $version = 'UNKNOWN';
        if ($conditions->count() > 0) {
            $firstCondition = $conditions->first();
            if ($firstCondition->condition !== null && $firstCondition->condition !== '') {
                $version = 'OLD';
            } else {
                $version = 'NEW';
            }
        }

        if ($version === 'OLD') {
            return response()->json([
                'isValid' => false,
                'summary' => 'Versión antigua detectada. Imposible de validar.',
                'description' => 'Este cuestionario usa la versión antigua del sistema de condiciones. Debe ser corregido cuanto antes.',
                'details' => [[
                    'type' => 'warning',
                    'title' => 'Actualización recomendada',
                    'message' => 'Este cuestionario usa el formato antiguo de condiciones. Se recomienda migrar al nuevo formato para mejor funcionalidad y mantenimiento.',
                    'items' => ['Formato legacy detectado'],
                ]],
            ]);
        }

        if ($version === 'NEW') {
            return $this->validateNewFlow(collect($questions), $conditions);
        }

        return response()->json([
            'isValid' => false,
            'summary' => 'Versión desconocida',
            'description' => 'No se pudo determinar la versión del cuestionario',
            'details' => [[
                'type' => 'error',
                'title' => 'Versión no identificada',
                'message' => 'No se pudo determinar si el cuestionario usa el formato antiguo o nuevo',
            ]],
        ]);
    }

    public function saveEligibilityRequirements(Request $request, $ayudaId)
    {
        try {
            $request->validate([
                'requirements' => 'required|array',
                'requirements.*.description' => 'required|string',
                'requirements.*.type' => 'required|in:simple,group',
                'requirements.*.question_id' => 'required_if:requirements.*.type,simple|integer',
                'requirements.*.operator' => 'required_if:requirements.*.type,simple|string',
                'requirements.*.value' => 'required_if:requirements.*.type,simple',
                'requirements.*.groupLogic' => 'required_if:requirements.*.type,group|in:AND,OR',
                'requirements.*.rules' => 'required_if:requirements.*.type,group|array',
                'requirements.*.rules.*.question_id' => 'required|integer',
                'requirements.*.rules.*.operator' => 'required|string',
                'requirements.*.rules.*.value' => 'required',
            ]);

            // Eliminar requisitos existentes para esta ayuda
            AyudaRequisitoJson::where('ayuda_id', $ayudaId)->delete();

            // Crear el nuevo requisito con todos los criterios
            $jsonRegla = [
                'condition' => 'AND',
                'rules' => $request->input('requirements'),
            ];

            AyudaRequisitoJson::create([
                'ayuda_id' => $ayudaId,
                'descripcion' => 'Requisitos de elegibilidad del wizard',
                'json_regla' => $jsonRegla,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Requisitos de elegibilidad guardados correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los requisitos: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getAllQuestions()
    {
        try {
            $questions = Question::select('id', 'text', 'type', 'options', 'slug')
                ->where('type', '!=', 'end')
                ->orderBy('text')
                ->get()
                ->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'text' => $question->text,
                        'type' => $question->type,
                        'options' => $question->options ?: [],
                        'slug' => $question->slug,
                    ];
                });

            return response()->json([
                'success' => true,
                'questions' => $questions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preguntas: '.$e->getMessage(),
            ], 500);
        }
    }

    private function validateNewFlow($questions, $conditions)
    {
        $details = [];
        $isValid = true;

        // 1. Verificar que todas las preguntas existan
        $questionIds = $questions->pluck('id')->toArray();
        $conditionQuestionIds = $conditions->pluck('question_id')->unique()->toArray();
        $conditionNextQuestionIds = $conditions->pluck('next_question_id')->filter()->unique()->toArray();

        // Preguntas en condiciones que no existen
        $missingQuestions = array_diff($conditionQuestionIds, $questionIds);
        if (! empty($missingQuestions)) {
            $isValid = false;
            $details[] = [
                'type' => 'error',
                'title' => 'Preguntas faltantes',
                'message' => 'Hay condiciones que referencian preguntas que no existen',
                'items' => array_map(function ($id) {
                    return "Pregunta ID: $id";
                }, $missingQuestions),
            ];
        }

        // Preguntas de destino que no existen
        $missingNextQuestions = array_diff($conditionNextQuestionIds, $questionIds);
        if (! empty($missingNextQuestions)) {
            $isValid = false;
            $details[] = [
                'type' => 'error',
                'title' => 'Preguntas de destino faltantes',
                'message' => 'Hay condiciones que saltan a preguntas que no existen',
                'items' => array_map(function ($id) {
                    return "Pregunta ID: $id";
                }, $missingNextQuestions),
            ];
        }

        $questionsWithConditions = $conditions->pluck('question_id')->unique()->toArray();
        $questionsWithoutConditions = array_diff($questionIds, $questionsWithConditions);

        if (! empty($questionsWithoutConditions)) {
            $details[] = [
                'type' => 'warning',
                'title' => 'Preguntas sin condiciones',
                'message' => 'Hay preguntas que no tienen condiciones definidas (seguirán el orden normal sin saltos)',
                'items' => array_map(function ($id) {
                    return "Pregunta ID: $id";
                }, $questionsWithoutConditions),
            ];
        }

        // 4. Verificar que haya caminos de salida (next_question_id = null)
        $exitConditions = $conditions->where('next_question_id', null)->count();
        if ($exitConditions === 0) {
            $details[] = [
                'type' => 'warning',
                'title' => 'Sin caminos de salida',
                'message' => 'No hay condiciones que terminen el cuestionario (next_question_id = null)',
            ];
        }

        // 6. Verificar operadores válidos
        $validOperators = ['=', '!=', '>', '>=', '<', '<=', 'in', 'contains'];
        $invalidOperators = $conditions->filter(function ($condition) use ($validOperators) {
            return ! in_array($condition->operator, $validOperators);
        });

        if ($invalidOperators->count() > 0) {
            $isValid = false;
            $details[] = [
                'type' => 'error',
                'title' => 'Operadores inválidos',
                'message' => 'Hay condiciones con operadores no soportados',
                'items' => $invalidOperators->pluck('operator')->unique()->toArray(),
            ];
        }

        // Determinar resumen
        if ($isValid && empty($details)) {
            $summary = 'Flujo válido';
            $description = 'El cuestionario tiene una estructura correcta y todos los caminos son válidos';
        } elseif ($isValid) {
            $summary = 'Flujo válido con advertencias';
            $description = 'El cuestionario es funcional pero tiene algunas advertencias';
        } else {
            $summary = 'Flujo inválido';
            $description = 'El cuestionario tiene errores que deben corregirse';
        }

        return response()->json([
            'isValid' => $isValid,
            'summary' => $summary,
            'description' => $description,
            'details' => $details,
        ]);
    }

    /**
     * Obtener requisitos existentes de una ayuda
     */
    public function getRequisitos($ayudaId)
    {
        try {
            $ayuda = Ayuda::findOrFail($ayudaId);

            // Buscar requisitos existentes
            $requisitos = AyudaRequisitoJson::where('ayuda_id', $ayudaId)->get();

            if ($requisitos->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'requisitos' => [],
                    'message' => 'No hay requisitos configurados para esta ayuda',
                ]);
            }

            // Procesar cada requisito
            $requisitosProcesados = $requisitos->map(function ($requisito) {
                $jsonRegla = $requisito->json_regla;

                // Si json_regla ya es un array, usarlo directamente
                if (is_array($jsonRegla)) {
                    return [
                        'id' => $requisito->id,
                        'descripcion' => $requisito->descripcion,
                        'json_regla' => $jsonRegla,
                    ];
                }

                // Si es string, intentar decodificarlo
                if (is_string($jsonRegla)) {
                    $decoded = json_decode($jsonRegla, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return [
                            'id' => $requisito->id,
                            'descripcion' => $requisito->descripcion,
                            'json_regla' => $decoded,
                        ];
                    }
                }

                // Fallback: devolver el requisito tal como está
                return [
                    'id' => $requisito->id,
                    'descripcion' => $requisito->descripcion,
                    'json_regla' => $requisito->json_regla,
                ];
            });

            return response()->json([
                'success' => true,
                'requisitos' => $requisitosProcesados,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los requisitos: '.$e->getMessage(),
            ], 500);
        }
    }
}
