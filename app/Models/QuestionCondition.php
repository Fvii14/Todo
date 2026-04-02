<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionCondition extends Model
{
    protected $fillable = ['question_id', 'operator', 'value', 'next_question_id', 'questionnaire_id', 'order', 'composite_rules', 'composite_logic', 'is_composite'];

    protected $casts = [
        'value' => 'array',
        'composite_rules' => 'array',
        'is_composite' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function nextQuestion()
    {
        return $this->belongsTo(Question::class, 'next_question_id');
    }

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class, 'questionnaire_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuestionnaireConditionVersion::class, 'questionnaire_id', 'questionnaire_id');
    }

    public static function getActiveVersion($questionnaireId)
    {
        return QuestionnaireConditionVersion::getActiveVersion($questionnaireId);
    }

    public static function getCurrentDraft($questionnaireId)
    {
        return QuestionnaireConditionVersion::getCurrentDraft($questionnaireId);
    }

    public static function createDraft($questionnaireId, $description = null)
    {
        return QuestionnaireConditionVersion::createFromCurrentConditions($questionnaireId, $description);
    }

    /**
     * Obtiene las condiciones de un cuestionario en formato unificado.
     * Solo usa el formato nuevo (operator + value), sin soporte para legacy condition JSON.
     */
    public static function getConditions(int $questionnaireId): array
    {
        $conditions = self::where('questionnaire_id', $questionnaireId)
            ->orderBy('order')
            ->get();

        return $conditions->map(function ($condition) {
            $result = [
                'question_id' => $condition->question_id,
                'operator' => $condition->operator,
                'value' => $condition->value,
                'next_question_id' => $condition->next_question_id,
                'is_composite' => $condition->is_composite ?? false,
            ];

            // Si es compuesta, incluir las reglas
            if ($condition->is_composite && $condition->composite_rules) {
                $result['composite_rules'] = $condition->composite_rules;
                $result['composite_logic'] = $condition->composite_logic ?? 'AND';
            }

            return $result;
        })->toArray();
    }
}
