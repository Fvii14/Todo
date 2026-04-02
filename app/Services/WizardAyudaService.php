<?php

namespace App\Services;

use App\Models\Ayuda;
use App\Models\AyudaDocumento;
use App\Models\AyudaDocumentoConviviente;
use App\Models\AyudaPreRequisito;
use App\Models\AyudaPreRequisitoRule;
use App\Models\AyudaRequisitoJson;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use App\Models\Wizard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WizardAyudaService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function transformWizardToAyuda(Wizard $wizard): array
    {
        try {
            DB::beginTransaction();

            $data = $wizard->data;
            // Si en los datos del wizard ya viene una ayuda_id nos indica que estamos editando una ayuda existente,
            // de esta forma no se crea una nueva ayuda, sino que se actualiza la existente.
            $existingAyudaId = $data['ayuda']['id'] ?? $data['ayuda_id'] ?? null;
            $isEditing = false;
            $existingAyuda = null;

            if ($existingAyudaId) {
                $existingAyuda = Ayuda::find($existingAyudaId);
                $isEditing = (bool) $existingAyuda;
            }

            $ayuda = $this->createAyuda($data['ayuda'], $existingAyuda);

            // Si estamos editando, limpiamos la configuración previa (cuestionarios, docs, requisitos)
            if ($isEditing) {
                $this->resetAyudaConfiguration($ayuda->id);
            }

            // Crear cuestionario específico (principal) - tipo 'pre'
            $questionnaire = null;
            $questions = [];
            if (isset($data['questionnaire_specific']) && ! empty($data['questionnaire_specific']['name'])) {
                $questionnaire = $this->createQuestionnaire($data['questionnaire_specific'], $ayuda->id, 'pre');
                $questions = $this->createQuestions($data['questions_specific'] ?? [], $questionnaire->id);
                $this->createQuestionConditions($data['questionConditions_specific'] ?? [], $questionnaire->id, $questions);
                $ayuda->update(['questionnaire_id' => $questionnaire->id]);
            } elseif (isset($data['questionnaire']) && ! empty($data['questionnaire']['name'])) {
                // Compatibilidad con formato antiguo
                $questionnaire = $this->createQuestionnaire($data['questionnaire'], $ayuda->id, 'pre');
                $questions = $this->createQuestions($data['questions'] ?? [], $questionnaire->id);
                $this->createQuestionConditions($data['questionConditions'] ?? [], $questionnaire->id, $questions);
                $ayuda->update(['questionnaire_id' => $questionnaire->id]);
            }

            // Crear cuestionario de solicitante - tipo 'solicitud'
            $solicitanteQuestionnaire = null;
            $solicitanteQuestions = [];
            // Crear el cuestionario de solicitante si hay preguntas configuradas, incluso si el nombre está vacío
            $hasSolicitanteQuestions = ! empty($data['questions_solicitante']) && count($data['questions_solicitante']) > 0;
            if ($hasSolicitanteQuestions) {
                // Si no hay nombre, usar uno por defecto
                $questionnaireSolicitanteData = $data['questionnaire_solicitante'] ?? [];
                if (empty($questionnaireSolicitanteData['name'])) {
                    $questionnaireSolicitanteData['name'] = 'Cuestionario de Solicitante - '.$ayuda->nombre_ayuda;
                }
                $solicitanteQuestionnaire = $this->createQuestionnaire($questionnaireSolicitanteData, $ayuda->id, 'solicitud');
                $solicitanteQuestions = $this->createQuestions($data['questions_solicitante'] ?? [], $solicitanteQuestionnaire->id);
                $this->createQuestionConditions($data['questionConditions_solicitante'] ?? [], $solicitanteQuestionnaire->id, $solicitanteQuestions);
            } elseif (isset($data['questionnaire_solicitante']) && ! empty($data['questionnaire_solicitante']['name'])) {
                // Si hay nombre pero no preguntas, crear el cuestionario vacío (por si se agregan después)
                $solicitanteQuestionnaire = $this->createQuestionnaire($data['questionnaire_solicitante'], $ayuda->id, 'solicitud');
            }

            // Crear cuestionario de conviviente - tipo 'conviviente'
            $convivienteQuestionnaire = null;
            $convivienteQuestions = [];
            // Crear el cuestionario de convivientes si hay preguntas configuradas, incluso si el nombre está vacío
            $hasConvivienteQuestions = ! empty($data['questions_conviviente']) && count($data['questions_conviviente']) > 0;
            if ($hasConvivienteQuestions) {
                // Si no hay nombre, usar uno por defecto
                $questionnaireConvivienteData = $data['questionnaire_conviviente'] ?? [];
                if (empty($questionnaireConvivienteData['name'])) {
                    $questionnaireConvivienteData['name'] = 'Cuestionario de Convivientes - '.$ayuda->nombre_ayuda;
                }
                $convivienteQuestionnaire = $this->createQuestionnaire($questionnaireConvivienteData, $ayuda->id, 'conviviente');
                $convivienteQuestions = $this->createQuestions($data['questions_conviviente'] ?? [], $convivienteQuestionnaire->id);
                $this->createQuestionConditions($data['questionConditions_conviviente'] ?? [], $convivienteQuestionnaire->id, $convivienteQuestions);
            } elseif (isset($data['questionnaire_conviviente']) && ! empty($data['questionnaire_conviviente']['name'])) {
                // Si hay nombre pero no preguntas, crear el cuestionario vacío (por si se agregan después)
                $convivienteQuestionnaire = $this->createQuestionnaire($data['questionnaire_conviviente'], $ayuda->id, 'conviviente');
            }

            $this->createPreRequisitos($data['preRequisitos'] ?? [], $ayuda->id);
            $this->createEligibilityLogic($data['eligibilityLogic'] ?? [], $ayuda->id);
            $this->createDocuments($data['documents'] ?? [], $ayuda->id);
            $this->createDocumentsConvivientes($data['documents_convivientes'] ?? [], $ayuda->id);

            // Guardar el ID de la ayuda en el wizard para poder cargarla después
            $wizardData = $wizard->data ?? [];
            $wizardData['ayuda_id'] = $ayuda->id;
            $wizard->data = $wizardData;
            $wizard->markAsCompleted();

            DB::commit();

            return [
                'success' => true,
                'ayuda_id' => $ayuda->id,
                'questionnaire_id' => $questionnaire ? $questionnaire->id : null,
                'questions_count' => count($questions),
                'questionnaire_solicitante_id' => $solicitanteQuestionnaire ? $solicitanteQuestionnaire->id : null,
                'questions_solicitante_count' => count($solicitanteQuestions),
                'questionnaire_conviviente_id' => $convivienteQuestionnaire ? $convivienteQuestionnaire->id : null,
                'questions_conviviente_count' => count($convivienteQuestions),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error transformando wizard a ayuda: '.$e->getMessage(), [
                'wizard_id' => $wizard->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Crea una nueva ayuda o actualiza una existente si se proporciona.
     */
    private function createAyuda(array $ayudaData, ?Ayuda $existingAyuda = null): Ayuda
    {
        $organoId = $ayudaData['organo_id'];
        $ccaaId = ($organoId == 20) ? null : $organoId;

        if ($existingAyuda) {
            // Actualizamos la ayuda existente sin regenerar el slug
            $existingAyuda->update([
                'nombre_ayuda' => $ayudaData['nombre_ayuda'],
                'description' => $ayudaData['description'] ?? $existingAyuda->description,
                'sector' => $ayudaData['sector'],
                'presupuesto' => $ayudaData['presupuesto'] ?? $existingAyuda->presupuesto,
                'fecha_inicio' => $ayudaData['fecha_inicio'] ?? $existingAyuda->fecha_inicio,
                'fecha_fin' => $ayudaData['fecha_fin'] ?? $existingAyuda->fecha_fin,
                'fecha_inicio_periodo' => $ayudaData['fecha_inicio_periodo'] ?? $existingAyuda->fecha_inicio_periodo,
                'fecha_fin_periodo' => $ayudaData['fecha_fin_periodo'] ?? $existingAyuda->fecha_fin_periodo,
                'organo_id' => $organoId,
                'ccaa_id' => $ccaaId,
                'cuantia_usuario' => $ayudaData['cuantia_usuario'] ?? $existingAyuda->cuantia_usuario,
                'activo' => $ayudaData['activo'] ?? $existingAyuda->activo,
            ]);

            return $existingAyuda;
        }

        $slug = $this->generateSlug($ayudaData['nombre_ayuda']);

        return Ayuda::create([
            'nombre_ayuda' => $ayudaData['nombre_ayuda'],
            'slug' => $slug,
            'description' => $ayudaData['description'] ?? null,
            'sector' => $ayudaData['sector'],
            'presupuesto' => $ayudaData['presupuesto'] ?? null,
            'fecha_inicio' => $ayudaData['fecha_inicio'] ?? null,
            'fecha_fin' => $ayudaData['fecha_fin'] ?? null,
            'fecha_inicio_periodo' => $ayudaData['fecha_inicio_periodo'] ?? null,
            'fecha_fin_periodo' => $ayudaData['fecha_fin_periodo'] ?? null,
            'organo_id' => $organoId,
            'ccaa_id' => $ccaaId,
            'cuantia_usuario' => $ayudaData['cuantia_usuario'] ?? 0,
            'activo' => $ayudaData['activo'] ?? true,
            'create_time' => now(),
        ]);
    }

    /**
     * Elimina la configuración previa de una ayuda para poder recrearla
     * a partir del wizard al editar (cuestionarios, documentos, requisitos, etc.).
     */
    private function resetAyudaConfiguration(int $ayudaId): void
    {
        // Cuestionarios y sus preguntas/condiciones se eliminan en cascada si están bien definidas las relaciones
        $ayuda = Ayuda::with('questionnaires')->find($ayudaId);

        if ($ayuda && method_exists($ayuda, 'questionnaires')) {
            $ayuda->questionnaires()->delete();
        }

        AyudaPreRequisito::where('ayuda_id', $ayudaId)->delete();
        AyudaRequisitoJson::where('ayuda_id', $ayudaId)->delete();
        AyudaDocumento::where('ayuda_id', $ayudaId)->delete();
        AyudaDocumentoConviviente::where('ayuda_id', $ayudaId)->delete();
    }

    private function generateSlug(string $nombre): string
    {
        $slug = strtolower($nombre);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        if (empty($slug)) {
            $slug = 'ayuda-'.time();
        }

        $originalSlug = $slug;
        $counter = 1;
        while (Ayuda::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function createQuestionnaire(array $questionnaireData, int $ayudaId, string $tipo = 'pre'): Questionnaire
    {
        if (empty($questionnaireData['name'])) {
            throw new \InvalidArgumentException('El nombre del cuestionario es obligatorio');
        }

        return Questionnaire::create([
            'name' => $questionnaireData['name'],
            'active' => $questionnaireData['active'] ?? true,
            'redirect_url' => $questionnaireData['redirect_url'] ?? null,
            'ayuda_id' => $ayudaId,
            'tipo' => $questionnaireData['tipo'] ?? $tipo,
        ]);
    }

    private function createQuestions(array $questionsData, int $questionnaireId): array
    {
        $questions = [];

        foreach ($questionsData as $index => $questionData) {
            if (isset($questionData['id'])) {
                $question = Question::find($questionData['id']);
                if ($question) {
                    $questions[$question->id] = $question;

                    QuestionnaireQuestion::create([
                        'questionnaire_id' => $questionnaireId,
                        'question_id' => $question->id,
                        'orden' => $index + 1,
                    ]);
                }
            } else {
                $question = Question::create([
                    'slug' => $questionData['slug'] ?? Str::slug($questionData['text']),
                    'text' => $questionData['text'],
                    'type' => $questionData['type'],
                    'options' => $questionData['options'] ?? null,
                    'regex_id' => $questionData['regex_id'] ?? null,
                ]);

                QuestionnaireQuestion::create([
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $question->id,
                    'orden' => $index + 1,
                ]);

                $questions[$question->id] = $question;
            }
        }

        return $questions;
    }

    private function createQuestionConditions(array $conditionsData, int $questionnaireId, array $questions): void
    {
        foreach ($conditionsData as $conditionData) {
            $nextQuestionId = $this->processNextQuestionId($conditionData['next_question_id'] ?? null, $questions);

            if (isset($conditionData['rules']) && is_array($conditionData['rules']) && count($conditionData['rules']) > 1) {
                $this->createCompositeCondition($conditionData, $nextQuestionId, $questionnaireId);
            } else {
                $this->createSimpleCondition($conditionData, $nextQuestionId, $questionnaireId);
            }
        }
    }

    private function processNextQuestionId($nextQuestionId, array $questions): ?int
    {
        if (! $nextQuestionId || $nextQuestionId === 'FIN') {
            return null;
        }

        foreach ($questions as $question) {
            if ($question->id == $nextQuestionId) {
                return $question->id;
            }
        }

        return null;
    }

    private function createSimpleCondition(array $conditionData, ?int $nextQuestionId, int $questionnaireId): void
    {
        if (isset($conditionData['rules']) && is_array($conditionData['rules']) && ! empty($conditionData['rules'])) {
            $rule = $conditionData['rules'][0];
            $questionId = $rule['question_id'];
            $operator = $this->normalizeOperator($rule['operator'] ?? '==');
            $value = $this->normalizeValue($rule['value'] ?? '', $questionId);
        } else {
            $questionId = $conditionData['question_id'];
            $operator = $this->normalizeOperator($conditionData['operator'] ?? '==');
            $value = $this->normalizeValue($conditionData['value'] ?? '', $questionId);
        }

        QuestionCondition::create([
            'question_id' => $questionId,
            'operator' => $operator,
            'value' => $value,
            'next_question_id' => $nextQuestionId,
            'questionnaire_id' => $questionnaireId,
            'order' => $conditionData['order'] ?? 1,
            'is_composite' => false,
            'composite_rules' => null,
            'composite_logic' => 'AND',
        ]);
    }

    private function createCompositeCondition(array $conditionData, ?int $nextQuestionId, int $questionnaireId): void
    {
        $rules = [];
        $compositeLogic = 'AND';

        foreach ($conditionData['rules'] as $rule) {
            $rules[] = [
                'question_id' => $rule['question_id'],
                'operator' => $this->normalizeOperator($rule['operator'] ?? '=='),
                'value' => $this->normalizeValue($rule['value'] ?? '', $rule['question_id'] ?? null),
            ];

            if (empty($rules)) {
                $compositeLogic = $rule['connector'] ?? 'AND';
            }
        }

        QuestionCondition::create([
            'question_id' => $conditionData['question_id'],
            'operator' => '==',
            'value' => '',
            'next_question_id' => $nextQuestionId,
            'questionnaire_id' => $questionnaireId,
            'order' => $conditionData['order'] ?? 1,
            'is_composite' => true,
            'composite_rules' => $rules,
            'composite_logic' => $compositeLogic,
        ]);
    }

    /**
     * Normaliza un operador a formato estándar ('==' en vez de '=')
     */
    private function normalizeOperator($operator): string
    {
        if ($operator === '=') {
            return '==';
        }

        return $operator;
    }

    /**
     * Normaliza un valor según su tipo.
     * IMPORTANTE: Para select/multiple, mantiene el valor lógico (texto), NO convierte a índice.
     *
     * @param  mixed  $value
     * @param  int|null  $questionId  ID de la pregunta (opcional, para validar tipo)
     * @return mixed
     */
    private function normalizeValue($value, ?int $questionId = null)
    {
        // Normalizar booleanos
        if ($value === 'true' || $value === '1') {
            return 1;
        } elseif ($value === 'false' || $value === '0') {
            return 0;
        }

        // Para select/multiple: mantener el valor tal cual (ya viene como texto/clave del frontend)
        // NO convertir a índice de posición

        // Normalizar números (solo si es realmente numérico)
        if (is_numeric($value) && $value !== '') {
            // Verificar que no sea un string numérico que debería mantenerse como texto
            // (ej: códigos postales, números de teléfono)
            if (is_string($value) && strlen($value) > 0 && $value[0] === '0' && strlen($value) > 1) {
                // Mantener como string si empieza por 0 (probablemente no es un número puro)
                return $value;
            }

            return (float) $value;
        }

        return $value;
    }

    private function createPreRequisitos(array $preRequisitosData, int $ayudaId): void
    {
        foreach ($preRequisitosData as $index => $preReqData) {
            $preRequisito = AyudaPreRequisito::create([
                'ayuda_id' => $ayudaId,
                'name' => $preReqData['name'] ?? 'Pre-requisito '.($index + 1),
                'description' => $preReqData['description'] ?? null,
                'type' => $preReqData['type'] ?? 'simple',
                'target_type' => $preReqData['target_type'] ?? 'solicitante',
                'conviviente_type' => $preReqData['conviviente_type'] ?? null,
                'question_id' => $preReqData['question_id'] ?? null,
                'operator' => $preReqData['operator'] ?? null,
                'value' => $preReqData['value'] ?? null,
                'value2' => $preReqData['value2'] ?? null,
                'value_type' => $preReqData['value_type'] ?? 'exact',
                'age_unit' => $preReqData['age_unit'] ?? null,
                'group_logic' => $preReqData['group_logic'] ?? 'AND',
                'is_required' => $preReqData['required'] ?? true,
                'error_message' => $preReqData['error_message'] ?? null,
                'order' => $index + 1,
                'active' => $preReqData['active'] ?? true,
            ]);

            if ($preReqData['type'] === 'group' && isset($preReqData['rules']) && is_array($preReqData['rules'])) {
                $this->createPreRequisitoRules($preRequisito->id, $preReqData['rules']);
            }
        }
    }

    private function createPreRequisitoRules(int $preRequisitoId, array $rules): void
    {
        $this->processRulesRecursively($preRequisitoId, $rules);
    }

    private function processRulesRecursively(int $preRequisitoId, array $rules, int &$order = 0): void
    {
        foreach ($rules as $rule) {
            if (($rule['type'] ?? null) === 'group' && isset($rule['rules']) && is_array($rule['rules'])) {
                $this->processRulesRecursively($preRequisitoId, $rule['rules'], $order);

                continue;
            }

            if (! empty($rule['question_id'])) {
                $order++;
                AyudaPreRequisitoRule::create([
                    'pre_requisito_id' => $preRequisitoId,
                    'question_id' => $rule['question_id'],
                    'operator' => $rule['operator'] ?? null,
                    'value' => $rule['value'] ?? null,
                    'value2' => $rule['value2'] ?? null,
                    'value_type' => $rule['value_type'] ?? 'exact',
                    'age_unit' => $rule['age_unit'] ?? null,
                    'order' => $order,
                ]);
            } else {
                Log::warning('Saltando regla sin question_id', [
                    'pre_requisito_id' => $preRequisitoId,
                    'rule' => $rule,
                ]);
            }
        }
    }

    private function createEligibilityLogic(array $eligibilityData, int $ayudaId): void
    {
        foreach ($eligibilityData as $logicData) {
            if (isset($logicData['type']) && $logicData['type'] === 'simple') {
                $rule = [
                    'question_id' => $logicData['question_id'],
                    'operator' => $logicData['operator'],
                    'value' => $logicData['value'] === '"[null]"' ? '[null]' : $logicData['value'],
                ];
                // Propagar personType y value2 si existen
                if (! empty($logicData['personType'])) {
                    $rule['personType'] = $logicData['personType'];
                }
                if (! empty($logicData['convivienteType'])) {
                    $rule['convivienteType'] = $logicData['convivienteType'];
                }
                if ($logicData['operator'] === 'between' && ! empty($logicData['value2'])) {
                    $rule['value2'] = $logicData['value2'] === '"[null]"' ? '[null]' : $logicData['value2'];
                }
                if (! empty($logicData['valueType'])) {
                    $rule['valueType'] = $logicData['valueType'];
                }
                if (! empty($logicData['ageUnit'])) {
                    $rule['ageUnit'] = $logicData['ageUnit'];
                }

                $jsonRegla = [
                    'condition' => 'AND',
                    'rules' => [$rule],
                ];

                AyudaRequisitoJson::create([
                    'ayuda_id' => $ayudaId,
                    'descripcion' => $logicData['description'] ?? 'Requisito de elegibilidad',
                    'json_regla' => $jsonRegla,
                ]);
            } elseif (isset($logicData['type']) && $logicData['type'] === 'group') {
                $jsonRegla = $this->buildGroupJsonRegla($logicData);

                AyudaRequisitoJson::create([
                    'ayuda_id' => $ayudaId,
                    'descripcion' => $logicData['description'] ?? 'Grupo de requisitos de elegibilidad',
                    'json_regla' => $jsonRegla,
                ]);
            }
            // Formato legacy (si viene como descripcion + json_regla)
            elseif (isset($logicData['descripcion']) && isset($logicData['json_regla'])) {
                AyudaRequisitoJson::create([
                    'ayuda_id' => $ayudaId,
                    'descripcion' => $logicData['descripcion'],
                    'json_regla' => $logicData['json_regla'],
                ]);
            }
        }
    }

    private function buildGroupJsonRegla(array $groupData): array
    {
        $rules = [];

        if (isset($groupData['rules']) && is_array($groupData['rules'])) {
            foreach ($groupData['rules'] as $rule) {
                $ruleData = [
                    'question_id' => $rule['question_id'],
                    'operator' => $rule['operator'],
                    'value' => $rule['value'] === '"[null]"' ? '[null]' : $rule['value'],
                ];

                if (! empty($groupData['personType'])) {
                    $ruleData['personType'] = $groupData['personType'];
                }
                if (! empty($groupData['convivienteType'])) {
                    $ruleData['convivienteType'] = $groupData['convivienteType'];
                }
                if ($rule['operator'] === 'between' && ! empty($rule['value2'])) {
                    $ruleData['value2'] = $rule['value2'] === '"[null]"' ? '[null]' : $rule['value2'];
                }
                if (! empty($rule['valueType'])) {
                    $ruleData['valueType'] = $rule['valueType'];
                }
                if (! empty($rule['ageUnit'])) {
                    $ruleData['ageUnit'] = $rule['ageUnit'];
                }

                $rules[] = $ruleData;
            }
        }

        if (isset($groupData['subgroups']) && is_array($groupData['subgroups'])) {
            foreach ($groupData['subgroups'] as $subgroup) {
                $subgroupRules = [];

                if (isset($subgroup['rules']) && is_array($subgroup['rules'])) {
                    foreach ($subgroup['rules'] as $rule) {
                        $ruleData = [
                            'question_id' => $rule['question_id'],
                            'operator' => $rule['operator'],
                            'value' => $rule['value'] === '"[null]"' ? '[null]' : $rule['value'],
                        ];

                        if (! empty($subgroup['personType'])) {
                            $ruleData['personType'] = $subgroup['personType'];
                        } elseif (! empty($groupData['personType'])) {
                            $ruleData['personType'] = $groupData['personType'];
                        }
                        if (! empty($subgroup['convivienteType'])) {
                            $ruleData['convivienteType'] = $subgroup['convivienteType'];
                        } elseif (! empty($groupData['convivienteType'])) {
                            $ruleData['convivienteType'] = $groupData['convivienteType'];
                        }
                        if ($rule['operator'] === 'between' && ! empty($rule['value2'])) {
                            $ruleData['value2'] = $rule['value2'] === '"[null]"' ? '[null]' : $rule['value2'];
                        }
                        if (! empty($rule['valueType'])) {
                            $ruleData['valueType'] = $rule['valueType'];
                        }
                        if (! empty($rule['ageUnit'])) {
                            $ruleData['ageUnit'] = $rule['ageUnit'];
                        }
                        if (! empty($rule['discounts'])) {
                            $ruleData['discounts'] = $rule['discounts'];
                        }

                        $subgroupRules[] = $ruleData;
                    }
                }

                $nestedSubgroups = [];
                if (isset($subgroup['subgroups']) && is_array($subgroup['subgroups'])) {
                    foreach ($subgroup['subgroups'] as $nestedSubgroup) {
                        $nestedSubgroupRules = [];

                        if (isset($nestedSubgroup['rules']) && is_array($nestedSubgroup['rules'])) {
                            foreach ($nestedSubgroup['rules'] as $rule) {
                                $ruleData = [
                                    'question_id' => $rule['question_id'],
                                    'operator' => $rule['operator'],
                                    'value' => $rule['value'] === '"[null]"' ? '[null]' : $rule['value'],
                                ];

                                if (! empty($nestedSubgroup['personType'])) {
                                    $ruleData['personType'] = $nestedSubgroup['personType'];
                                } elseif (! empty($subgroup['personType'])) {
                                    $ruleData['personType'] = $subgroup['personType'];
                                } elseif (! empty($groupData['personType'])) {
                                    $ruleData['personType'] = $groupData['personType'];
                                }
                                if (! empty($nestedSubgroup['convivienteType'])) {
                                    $ruleData['convivienteType'] = $nestedSubgroup['convivienteType'];
                                } elseif (! empty($subgroup['convivienteType'])) {
                                    $ruleData['convivienteType'] = $subgroup['convivienteType'];
                                } elseif (! empty($groupData['convivienteType'])) {
                                    $ruleData['convivienteType'] = $groupData['convivienteType'];
                                }
                                if ($rule['operator'] === 'between' && ! empty($rule['value2'])) {
                                    $ruleData['value2'] = $rule['value2'] === '"[null]"' ? '[null]' : $rule['value2'];
                                }
                                if (! empty($rule['valueType'])) {
                                    $ruleData['valueType'] = $rule['valueType'];
                                }
                                if (! empty($rule['ageUnit'])) {
                                    $ruleData['ageUnit'] = $rule['ageUnit'];
                                }
                                if (! empty($rule['discounts'])) {
                                    $ruleData['discounts'] = $rule['discounts'];
                                }

                                $nestedSubgroupRules[] = $ruleData;
                            }
                        }

                        if (! empty($nestedSubgroupRules)) {
                            $nestedSubgroupStructure = [
                                'condition' => $nestedSubgroup['groupLogic'] ?? 'AND',
                                'rules' => $nestedSubgroupRules,
                            ];

                            if (! empty($nestedSubgroup['description'])) {
                                $nestedSubgroupStructure['description'] = $nestedSubgroup['description'];
                            }

                            $nestedSubgroups[] = $nestedSubgroupStructure;
                        }
                    }
                }

                if (! empty($subgroupRules) || ! empty($nestedSubgroups)) {
                    $subgroupStructure = [
                        'condition' => $subgroup['groupLogic'] ?? 'AND',
                        'rules' => $subgroupRules,
                    ];

                    if (! empty($subgroup['description'])) {
                        $subgroupStructure['description'] = $subgroup['description'];
                    }
                    if (! empty($subgroup['personType'])) {
                        $subgroupStructure['personType'] = $subgroup['personType'];
                    }
                    if (! empty($subgroup['convivienteType'])) {
                        $subgroupStructure['convivienteType'] = $subgroup['convivienteType'];
                    }
                    if (! empty($nestedSubgroups)) {
                        $subgroupStructure['subgroups'] = $nestedSubgroups;
                    }

                    $rules[] = $subgroupStructure;
                }
            }
        }

        return [
            'condition' => $groupData['groupLogic'] ?? 'AND',
            'rules' => $rules,
        ];
    }

    private function createDocuments(array $documentsData, int $ayudaId): void
    {
        foreach ($documentsData as $docData) {
            if (empty($docData['document_id'])) {
                continue;
            }

            // Estructurar condiciones como en elegibilidad: wrapper con condition y requirements
            $conditionsData = null;
            if (! empty($docData['conditions'])) {
                // Si ya viene en formato nuevo { condition, requirements }
                if (isset($docData['conditions']['condition']) && isset($docData['conditions']['requirements'])) {
                    $conditionsData = $docData['conditions'];
                }
                // Si viene como array de requisitos, crear wrapper
                elseif (is_array($docData['conditions'])) {
                    $conditionsData = [
                        'condition' => $docData['conditions_logic'] ?? 'AND',
                        'requirements' => $docData['conditions'],
                    ];
                }
            }

            AyudaDocumento::create([
                'ayuda_id' => $ayudaId,
                'documento_id' => $docData['document_id'],
                'es_obligatorio' => $docData['es_obligatorio'] ?? true,
                'conditions' => $conditionsData,
            ]);
        }
    }

    private function createDocumentsConvivientes(array $documentsData, int $ayudaId): void
    {
        foreach ($documentsData as $docData) {
            if (empty($docData['document_id'])) {
                continue;
            }

            // Estructurar condiciones como en elegibilidad: wrapper con condition y requirements
            $conditionsData = null;
            if (! empty($docData['conditions'])) {
                // Si ya viene en formato nuevo { condition, requirements }
                if (isset($docData['conditions']['condition']) && isset($docData['conditions']['requirements'])) {
                    $conditionsData = $docData['conditions'];
                }
                // Si viene como array de requisitos, crear wrapper
                elseif (is_array($docData['conditions'])) {
                    $conditionsData = [
                        'condition' => $docData['conditions_logic'] ?? 'AND',
                        'requirements' => $docData['conditions'],
                    ];
                }
            }

            AyudaDocumentoConviviente::create([
                'ayuda_id' => $ayudaId,
                'documento_id' => $docData['document_id'],
                'es_obligatorio' => $docData['es_obligatorio'] ?? true,
                'conditions' => $conditionsData,
            ]);
        }
    }

    public function validateWizardData(array $data): array
    {
        $errors = [];

        if (! isset($data['ayuda'])) {
            $errors[] = 'Faltan los datos de la ayuda';
        } else {
            $ayudaData = $data['ayuda'];
            if (empty($ayudaData['nombre_ayuda'])) {
                $errors[] = 'El nombre de la ayuda es obligatorio';
            }
            if (empty($ayudaData['sector'])) {
                $errors[] = 'El sector es obligatorio';
            }
            if (empty($ayudaData['organo_id'])) {
                $errors[] = 'El órgano es obligatorio';
            }
        }

        if (! isset($data['questionnaire'])) {
            $errors[] = 'Faltan los datos del cuestionario';
        } else {
            $questionnaireData = $data['questionnaire'];
            if (empty($questionnaireData['name'])) {
                $errors[] = 'El nombre del cuestionario es obligatorio';
            }
        }

        if (! isset($data['questions']) || empty($data['questions'])) {
            $errors[] = 'Debe haber al menos una pregunta';
        } else {
            foreach ($data['questions'] as $index => $question) {
                if (empty($question['text'])) {
                    $errors[] = 'La pregunta '.($index + 1).' debe tener texto';
                }
                if (empty($question['type'])) {
                    $errors[] = 'La pregunta '.($index + 1).' debe tener un tipo';
                }
            }
        }

        return $errors;
    }

    /**
     * Transforma una Ayuda existente de vuelta al formato del wizard.
     * Esto permite cargar los datos de una ayuda ya creada para editarla.
     */
    public function transformAyudaToWizard(Ayuda $ayuda): array
    {
        // Cargar todas las relaciones necesarias
        $ayuda->load([
            'questionnaires.questions',
            'questionnaires.questionConditions',
            'preRequisitos.rules',
            'ayudaRequisitosJson',
            'ayudaDocumentos.documento',
            'ayudaDocumentosConvivientes.documento',
        ]);

        $wizardData = [
            'ayuda' => [
                'id' => $ayuda->id,
                'nombre_ayuda' => $ayuda->nombre_ayuda,
                'slug' => $ayuda->slug,
                'description' => $ayuda->description,
                'sector' => $ayuda->sector,
                'presupuesto' => $ayuda->presupuesto,
                'fecha_inicio' => $ayuda->fecha_inicio ? ($ayuda->fecha_inicio instanceof \Carbon\Carbon ? $ayuda->fecha_inicio->format('Y-m-d') : $ayuda->fecha_inicio) : null,
                'fecha_fin' => $ayuda->fecha_fin ? ($ayuda->fecha_fin instanceof \Carbon\Carbon ? $ayuda->fecha_fin->format('Y-m-d') : $ayuda->fecha_fin) : null,
                'fecha_inicio_periodo' => $ayuda->fecha_inicio_periodo ? ($ayuda->fecha_inicio_periodo instanceof \Carbon\Carbon ? $ayuda->fecha_inicio_periodo->format('Y-m-d') : $ayuda->fecha_inicio_periodo) : null,
                'fecha_fin_periodo' => $ayuda->fecha_fin_periodo ? ($ayuda->fecha_fin_periodo instanceof \Carbon\Carbon ? $ayuda->fecha_fin_periodo->format('Y-m-d') : $ayuda->fecha_fin_periodo) : null,
                'organo_id' => $ayuda->organo_id,
                'cuantia_usuario' => $ayuda->cuantia_usuario,
                'activo' => $ayuda->activo ?? true,
            ],
        ];

        // Cuestionario principal (tipo 'pre')
        $questionnairePre = $ayuda->questionnaires()->where('tipo', 'pre')->first();
        if ($questionnairePre) {
            $wizardData['questionnaire_specific'] = [
                'id' => $questionnairePre->id,
                'name' => $questionnairePre->name,
                'active' => $questionnairePre->active,
                'redirect_url' => $questionnairePre->redirect_url,
                'tipo' => $questionnairePre->tipo,
            ];

            $questions = $questionnairePre->questions()->orderBy('questionnaire_questions.orden')->get();
            $wizardData['questions_specific'] = $questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'slug' => $question->slug,
                    'text' => $question->text,
                    'type' => $question->type,
                    'options' => $question->options,
                    'regex_id' => $question->regex_id,
                ];
            })->toArray();

            // Condiciones de las preguntas
            $conditions = $questionnairePre->questionConditions()->orderBy('order')->get();
            $wizardData['questionConditions_specific'] = $conditions->map(function ($condition) {
                $conditionData = [
                    'question_id' => $condition->question_id,
                    'operator' => $condition->operator,
                    'value' => $condition->value,
                    'next_question_id' => $condition->next_question_id,
                    'order' => $condition->order,
                ];

                if ($condition->is_composite) {
                    $conditionData['rules'] = $condition->composite_rules ?? [];
                    $conditionData['connector'] = $condition->composite_logic ?? 'AND';
                }

                return $conditionData;
            })->toArray();
        }

        // Cuestionario de solicitante (tipo 'solicitud')
        $questionnaireSolicitante = $ayuda->questionnaires()->where('tipo', 'solicitud')->first();
        if ($questionnaireSolicitante) {
            $wizardData['questionnaire_solicitante'] = [
                'id' => $questionnaireSolicitante->id,
                'name' => $questionnaireSolicitante->name,
                'active' => $questionnaireSolicitante->active,
                'redirect_url' => $questionnaireSolicitante->redirect_url,
                'tipo' => $questionnaireSolicitante->tipo,
            ];

            $questionsSolicitante = $questionnaireSolicitante->questions()->orderBy('questionnaire_questions.orden')->get();
            $wizardData['questions_solicitante'] = $questionsSolicitante->map(function ($question) {
                return [
                    'id' => $question->id,
                    'slug' => $question->slug,
                    'text' => $question->text,
                    'type' => $question->type,
                    'options' => $question->options,
                    'regex_id' => $question->regex_id,
                ];
            })->toArray();

            $conditionsSolicitante = $questionnaireSolicitante->questionConditions()->orderBy('order')->get();
            $wizardData['questionConditions_solicitante'] = $conditionsSolicitante->map(function ($condition) {
                $conditionData = [
                    'question_id' => $condition->question_id,
                    'operator' => $condition->operator,
                    'value' => $condition->value,
                    'next_question_id' => $condition->next_question_id,
                    'order' => $condition->order,
                ];

                if ($condition->is_composite) {
                    $conditionData['rules'] = $condition->composite_rules ?? [];
                    $conditionData['connector'] = $condition->composite_logic ?? 'AND';
                }

                return $conditionData;
            })->toArray();
        }

        // Cuestionario de conviviente (tipo 'conviviente')
        $questionnaireConviviente = $ayuda->questionnaires()->where('tipo', 'conviviente')->first();
        if ($questionnaireConviviente) {
            $wizardData['questionnaire_conviviente'] = [
                'id' => $questionnaireConviviente->id,
                'name' => $questionnaireConviviente->name,
                'active' => $questionnaireConviviente->active,
                'redirect_url' => $questionnaireConviviente->redirect_url,
                'tipo' => $questionnaireConviviente->tipo,
            ];

            $questionsConviviente = $questionnaireConviviente->questions()->orderBy('questionnaire_questions.orden')->get();
            $wizardData['questions_conviviente'] = $questionsConviviente->map(function ($question) {
                return [
                    'id' => $question->id,
                    'slug' => $question->slug,
                    'text' => $question->text,
                    'type' => $question->type,
                    'options' => $question->options,
                    'regex_id' => $question->regex_id,
                ];
            })->toArray();

            $conditionsConviviente = $questionnaireConviviente->questionConditions()->orderBy('order')->get();
            $wizardData['questionConditions_conviviente'] = $conditionsConviviente->map(function ($condition) {
                $conditionData = [
                    'question_id' => $condition->question_id,
                    'operator' => $condition->operator,
                    'value' => $condition->value,
                    'next_question_id' => $condition->next_question_id,
                    'order' => $condition->order,
                ];

                if ($condition->is_composite) {
                    $conditionData['rules'] = $condition->composite_rules ?? [];
                    $conditionData['connector'] = $condition->composite_logic ?? 'AND';
                }

                return $conditionData;
            })->toArray();
        }

        // Pre-requisitos
        $preRequisitos = $ayuda->preRequisitos()->orderBy('order')->get();
        $wizardData['preRequisitos'] = $preRequisitos->map(function ($preReq) {
            $preReqData = [
                'id' => $preReq->id,
                'name' => $preReq->name,
                'description' => $preReq->description,
                'type' => $preReq->type,
                'target_type' => $preReq->target_type,
                'conviviente_type' => $preReq->conviviente_type,
                'question_id' => $preReq->question_id,
                'operator' => $preReq->operator,
                'value' => $preReq->value,
                'value2' => $preReq->value2,
                'value_type' => $preReq->value_type,
                'age_unit' => $preReq->age_unit,
                'group_logic' => $preReq->group_logic,
                'required' => $preReq->is_required,
                'error_message' => $preReq->error_message,
                'active' => $preReq->active,
            ];

            if ($preReq->type === 'group' && $preReq->rules) {
                $preReqData['rules'] = $preReq->rules->map(function ($rule) {
                    return [
                        'question_id' => $rule->question_id,
                        'operator' => $rule->operator,
                        'value' => $rule->value,
                        'value2' => $rule->value2,
                        'value_type' => $rule->value_type,
                        'age_unit' => $rule->age_unit,
                    ];
                })->toArray();
            }

            return $preReqData;
        })->toArray();

        // Lógica de elegibilidad
        $eligibilityLogic = AyudaRequisitoJson::where('ayuda_id', $ayuda->id)->get();
        $wizardData['eligibilityLogic'] = $eligibilityLogic->map(function ($req) {
            $jsonRegla = $req->json_regla;
            $logicData = [
                'id' => $req->id,
                'description' => $req->descripcion,
            ];

            // Determinar si es simple o grupo
            if (isset($jsonRegla['rules']) && count($jsonRegla['rules']) === 1) {
                $logicData['type'] = 'simple';
                $rule = $jsonRegla['rules'][0];
                $logicData['question_id'] = $rule['question_id'];
                $logicData['operator'] = $rule['operator'];
                $logicData['value'] = $rule['value'];
                $logicData['value2'] = $rule['value2'] ?? null;
                $logicData['personType'] = $rule['personType'] ?? null;
                $logicData['convivienteType'] = $rule['convivienteType'] ?? null;
                $logicData['valueType'] = $rule['valueType'] ?? null;
                $logicData['ageUnit'] = $rule['ageUnit'] ?? null;
            } else {
                $logicData['type'] = 'group';
                $logicData['groupLogic'] = $jsonRegla['condition'] ?? 'AND';
                $logicData['rules'] = $jsonRegla['rules'] ?? [];
                $logicData['subgroups'] = $jsonRegla['subgroups'] ?? [];
            }

            return $logicData;
        })->toArray();

        // Documentos
        $documentos = $ayuda->ayudaDocumentos()->with('documento')->get();
        $wizardData['documents'] = $documentos->map(function ($doc) {
            $docData = [
                'id' => $doc->id,
                'document_id' => $doc->documento_id,
                'es_obligatorio' => $doc->es_obligatorio,
            ];

            if ($doc->conditions) {
                $docData['conditions'] = $doc->conditions;
            }

            return $docData;
        })->toArray();

        // Documentos de convivientes
        $documentosConvivientes = $ayuda->ayudaDocumentosConvivientes()->with('documento')->get();
        $wizardData['documents_convivientes'] = $documentosConvivientes->map(function ($doc) {
            $docData = [
                'id' => $doc->id,
                'document_id' => $doc->documento_id,
                'es_obligatorio' => $doc->es_obligatorio,
            ];

            if ($doc->conditions) {
                $docData['conditions'] = $doc->conditions;
            }

            return $docData;
        })->toArray();

        return $wizardData;
    }

    public function getExpectedDataStructure(): array
    {
        return [
            'ayuda' => [
                'nombre_ayuda' => 'string',
                'slug' => 'string|null',
                'description' => 'string|null',
                'sector' => 'string',
                'presupuesto' => 'float|null',
                'fecha_inicio' => 'date|null',
                'fecha_fin' => 'date|null',
                'fecha_inicio_periodo' => 'date|null',
                'fecha_fin_periodo' => 'date|null',
                'organo_id' => 'integer',
                'cuantia_usuario' => 'float',
                'activo' => 'boolean',
            ],
            'questionnaire' => [
                'name' => 'string',
                'active' => 'boolean',
                'redirect_url' => 'string|null',
                'tipo' => 'string',
            ],
            'questions' => [
                [
                    'id' => 'integer',
                    'slug' => 'string',
                    'text' => 'string',
                    'type' => 'string',
                    'options' => 'array|null',
                    'regex_id' => 'integer|null',
                ],
            ],
            'questionConditions' => [
                [
                    'question_id' => 'integer',
                    'operator' => 'string',
                    'value' => 'mixed',
                    'next_question_id' => 'integer|null',
                    'order' => 'integer',
                ],
            ],
            'eligibilityLogic' => [
                [
                    'descripcion' => 'string',
                    'json_regla' => 'array',
                ],
            ],
            'preRequisitos' => [
                [
                    'name' => 'string',
                    'description' => 'string|null',
                    'type' => 'string',
                    'target_type' => 'string',
                    'conviviente_type' => 'string|null',
                    'question_id' => 'integer|null',
                    'operator' => 'string|null',
                    'value' => 'mixed',
                    'value2' => 'mixed|null',
                    'value_type' => 'string|null',
                    'age_unit' => 'string|null',
                    'group_logic' => 'string|null',
                    'required' => 'boolean',
                    'error_message' => 'string|null',
                    'active' => 'boolean',
                ],
            ],
        ];
    }
}
