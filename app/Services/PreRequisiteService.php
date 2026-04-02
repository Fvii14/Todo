<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\AyudaPreRequisito;
use App\Models\Conviviente;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PreRequisiteService
{
    public function checkUserEligibility(User $user, int $ayudaId): array
    {
        $preRequisitos = AyudaPreRequisito::where('ayuda_id', $ayudaId)
            ->where('active', true)
            ->ordered()
            ->with(['question', 'groupRules.question'])
            ->get();

        $results = [];
        $allPassed = true;

        foreach ($preRequisitos as $preRequisito) {
            $result = $this->checkPreRequisite($user, $preRequisito);
            $results[] = $result;

            if ($preRequisito->is_required && ! $result['passed']) {
                $allPassed = false;
            }
        }

        return [
            'eligible' => $allPassed,
            'pre_requisitos' => $results,
            'passed_count' => collect($results)->where('passed', true)->count(),
            'total_count' => count($results),
            'required_failed' => collect($results)->where('required', true)->where('passed', false)->count(),
        ];
    }

    public function checkPreRequisite(User $user, AyudaPreRequisito $preRequisito): array
    {
        $result = [
            'id' => $preRequisito->id,
            'name' => $preRequisito->name,
            'description' => $preRequisito->description,
            'type' => $preRequisito->type,
            'target_type' => $preRequisito->target_type,
            'required' => $preRequisito->is_required,
            'passed' => false,
            'error_message' => null,
            'details' => [],
        ];

        try {
            if ($preRequisito->isSimple()) {
                $result = $this->checkSimplePreRequisite($user, $preRequisito, $result);
            } elseif ($preRequisito->isGroup()) {
                $result = $this->checkGroupPreRequisite($user, $preRequisito, $result);
            }
        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['error_message'] = 'Error al verificar el pre-requisito: '.$e->getMessage();
        }

        return $result;
    }

    private function checkSimplePreRequisite(User $user, AyudaPreRequisito $preRequisito, array $result): array
    {
        $answers = $this->getAnswersForTarget($user, $preRequisito);
        $question = $preRequisito->question;

        if (! $question) {
            $result['error_message'] = 'Pregunta no encontrada';

            return $result;
        }

        $passed = false;
        $details = [];

        foreach ($answers as $answer) {
            $answerValue = $this->normalizeAnswerValue($answer, $question);
            $expectedValue = $this->normalizeExpectedValue($preRequisito->value, $question);

            $checkResult = $this->compareValues($answerValue, $expectedValue, $preRequisito->operator, $preRequisito->value2);

            $details[] = [
                'answer_id' => $answer->id,
                'answer_value' => $answerValue,
                'expected_value' => $expectedValue,
                'operator' => $preRequisito->operator,
                'passed' => $checkResult,
            ];

            if ($checkResult) {
                $passed = true;
                break;
            }
        }

        $result['passed'] = $passed;
        $result['details'] = $details;

        if (! $passed && $preRequisito->error_message) {
            $result['error_message'] = $preRequisito->error_message;
        }

        return $result;
    }

    private function checkGroupPreRequisite(User $user, AyudaPreRequisito $preRequisito, array $result): array
    {
        $groupRules = $preRequisito->groupRules()->ordered()->get();
        $ruleResults = [];
        $allPassed = true;
        $anyPassed = false;

        foreach ($groupRules as $rule) {
            $ruleResult = $this->checkGroupRule($user, $rule, $preRequisito->target_type);
            $ruleResults[] = $ruleResult;

            if ($ruleResult['passed']) {
                $anyPassed = true;
            } else {
                $allPassed = false;
            }
        }

        $passed = ($preRequisito->group_logic === 'AND') ? $allPassed : $anyPassed;

        $result['passed'] = $passed;
        $result['details'] = $ruleResults;
        $result['group_logic'] = $preRequisito->group_logic;

        return $result;
    }

    private function checkGroupRule(User $user, $rule, string $targetType): array
    {
        $answers = $this->getAnswersForTarget($user, (object) ['target_type' => $targetType], $rule->question_id);
        $question = $rule->question;

        if (! $question) {
            return [
                'rule_id' => $rule->id,
                'question_text' => 'Pregunta no encontrada',
                'passed' => false,
                'error' => 'Pregunta no encontrada',
            ];
        }

        $passed = false;
        $details = [];

        foreach ($answers as $answer) {
            $answerValue = $this->normalizeAnswerValue($answer, $question);
            $expectedValue = $this->normalizeExpectedValue($rule->value, $question);

            $checkResult = $this->compareValues($answerValue, $expectedValue, $rule->operator, $rule->value2);

            $details[] = [
                'answer_id' => $answer->id,
                'answer_value' => $answerValue,
                'expected_value' => $expectedValue,
                'operator' => $rule->operator,
                'passed' => $checkResult,
            ];

            if ($checkResult) {
                $passed = true;
                break;
            }
        }

        return [
            'rule_id' => $rule->id,
            'question_text' => $question->text,
            'passed' => $passed,
            'details' => $details,
        ];
    }

    private function getAnswersForTarget(User $user, $preRequisito, ?int $questionId = null): Collection
    {
        $query = Answer::query();

        switch ($preRequisito->target_type) {
            case 'solicitante':
                $query->where('user_id', $user->id);
                break;

            case 'conviviente':
                $convivienteIds = Conviviente::where('user_id', $user->id)->pluck('id');
                $query->whereIn('conviviente_id', $convivienteIds);
                break;

            case 'unidad_convivencia_completa':
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('conviviente', function ($convQuery) use ($user) {
                            $convQuery->where('user_id', $user->id);
                        })
                        ->orWhereHas('userConviviente', function ($userConvQuery) use ($user) {
                            $userConvQuery->whereHas('onboarder', function ($onboarderQuery) use ($user) {
                                $onboarderQuery->where('user_id', $user->id);
                            });
                        });
                });
                break;

            case 'unidad_convivencia_sin_solicitante':
                $query->where(function ($q) use ($user) {
                    $q->whereHas('conviviente', function ($convQuery) use ($user) {
                        $convQuery->where('user_id', $user->id);
                    })
                        ->orWhereHas('userConviviente', function ($userConvQuery) use ($user) {
                            $userConvQuery->whereHas('onboarder', function ($onboarderQuery) use ($user) {
                                $onboarderQuery->where('user_id', $user->id);
                            });
                        });
                });
                break;

            case 'any_conviviente':
                $query->where(function ($q) use ($user) {
                    $q->whereHas('conviviente', function ($convQuery) use ($user) {
                        $convQuery->where('user_id', $user->id);
                    })
                        ->orWhereHas('userConviviente', function ($userConvQuery) use ($user) {
                            $userConvQuery->whereHas('onboarder', function ($onboarderQuery) use ($user) {
                                $onboarderQuery->where('user_id', $user->id);
                            });
                        });
                });
                break;

            case 'unidad_familiar_completa':
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('conviviente', function ($convQuery) use ($user) {
                            $convQuery->where('user_id', $user->id)
                                ->whereIn('tipo', ['conyuge', 'hijo', 'padre', 'otro']);
                        })
                        ->orWhereHas('userConviviente', function ($userConvQuery) use ($user) {
                            $userConvQuery->whereHas('onboarder', function ($onboarderQuery) use ($user) {
                                $onboarderQuery->where('user_id', $user->id);
                            })
                                ->whereIn('tipo', ['conyuge', 'hijo', 'padre', 'otro']);
                        });
                });
                break;

            case 'unidad_familiar_sin_solicitante':
                $query->where(function ($q) use ($user) {
                    $q->whereHas('conviviente', function ($convQuery) use ($user) {
                        $convQuery->where('user_id', $user->id)
                            ->whereIn('tipo', ['conyuge', 'hijo', 'padre', 'otro']);
                    })
                        ->orWhereHas('userConviviente', function ($userConvQuery) use ($user) {
                            $userConvQuery->whereHas('onboarder', function ($onboarderQuery) use ($user) {
                                $onboarderQuery->where('user_id', $user->id);
                            })
                                ->whereIn('tipo', ['conyuge', 'hijo', 'padre', 'otro']);
                        });
                });
                break;

            case 'any_familiar':
                $query->where(function ($q) use ($user) {
                    $q->whereHas('conviviente', function ($convQuery) use ($user) {
                        $convQuery->where('user_id', $user->id)
                            ->whereIn('tipo', ['conyuge', 'hijo', 'padre', 'otro']);
                    })
                        ->orWhereHas('userConviviente', function ($userConvQuery) use ($user) {
                            $userConvQuery->whereHas('onboarder', function ($onboarderQuery) use ($user) {
                                $onboarderQuery->where('user_id', $user->id);
                            })
                                ->whereIn('tipo', ['conyuge', 'hijo', 'padre', 'otro']);
                        });
                });
                break;

            case 'any_persona_unidad':
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('conviviente', function ($convQuery) use ($user) {
                            $convQuery->where('user_id', $user->id);
                        })
                        ->orWhereHas('userConviviente', function ($userConvQuery) use ($user) {
                            $userConvQuery->whereHas('onboarder', function ($onboarderQuery) use ($user) {
                                $onboarderQuery->where('user_id', $user->id);
                            });
                        });
                });
                break;
        }

        if ($questionId) {
            $query->where('question_id', $questionId);
        } elseif (isset($preRequisito->question_id)) {
            $query->where('question_id', $preRequisito->question_id);
        }

        return $query->get();
    }

    private function normalizeAnswerValue(Answer $answer, $question)
    {
        $value = $answer->answer;

        switch ($question->type) {
            case 'boolean':
                return in_array($value, ['1', 'true', 'yes', 'sí', 'si'], true);
            case 'integer':
                return (int) $value;
            case 'date':
                return $value;
            case 'select':
            case 'multiple':
                return $value;
            default:
                return $value;
        }
    }

    private function normalizeExpectedValue($value, $question)
    {
        if (is_array($value)) {
            return $value;
        }

        switch ($question->type) {
            case 'boolean':
                return in_array($value, ['1', 'true', 'yes', 'sí', 'si'], true);
            case 'integer':
                return (int) $value;
            default:
                return $value;
        }
    }

    private function compareValues($answerValue, $expectedValue, string $operator, $value2 = null): bool
    {
        switch ($operator) {
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
                return is_string($answerValue) && is_string($expectedValue) &&
                    stripos($answerValue, $expectedValue) !== false;
            case 'not_contains':
                return is_string($answerValue) && is_string($expectedValue) &&
                    stripos($answerValue, $expectedValue) === false;
            case 'between':
                return $answerValue >= $expectedValue && $answerValue <= $value2;
            case 'in':
                return is_array($expectedValue) && in_array($answerValue, $expectedValue);
            case 'not_in':
                return is_array($expectedValue) && ! in_array($answerValue, $expectedValue);
            case 'exists':
                return ! empty($answerValue);
            case 'not_exists':
                return empty($answerValue);
            default:
                return false;
        }
    }

    public function getPreRequisitosForAyuda(int $ayudaId): EloquentCollection
    {
        return AyudaPreRequisito::where('ayuda_id', $ayudaId)
            ->where('active', true)
            ->with(['question', 'groupRules.question'])
            ->ordered()
            ->get();
    }

    public function createSimplePreRequisite(array $data): AyudaPreRequisito
    {
        return AyudaPreRequisito::create(array_merge($data, [
            'type' => 'simple',
        ]));
    }

    public function createGroupPreRequisite(array $data, array $rules): AyudaPreRequisito
    {
        $preRequisito = AyudaPreRequisito::create(array_merge($data, [
            'type' => 'group',
        ]));

        foreach ($rules as $index => $rule) {
            $preRequisito->groupRules()->create(array_merge($rule, [
                'order' => $index,
            ]));
        }

        return $preRequisito->load('groupRules.question');
    }
}
