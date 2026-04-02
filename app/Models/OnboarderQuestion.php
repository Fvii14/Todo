<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboarderQuestion extends Model
{
    protected $fillable = [
        'onboarder_id',
        'onboarder_section_id',
        'question_id',
        'order',
        'screen',
        'condition',
        'required_condition',
        'optional_condition',
        'block_if_bankflip_filled',
        'hide_if_bankflip_filled',
        'show_if_bankflip_filled',
        'is_builder',
        'conditional_options',
        'selected_options',
    ];

    protected $casts = [
        'condition' => 'array',
        'required_condition' => 'array',
        'optional_condition' => 'array',
        'screen' => 'integer',
        'block_if_bankflip_filled' => 'boolean',
        'hide_if_bankflip_filled' => 'boolean',
        'show_if_bankflip_filled' => 'boolean',
        'is_builder' => 'boolean',
        'conditional_options' => 'array',
        'selected_options' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(OnboarderSection::class, 'onboarder_section_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function onboarder(): BelongsTo
    {
        return $this->belongsTo(Onboarder::class);
    }

    public function scopeForSection($query, int $sectionId)
    {
        return $query->where('onboarder_section_id', $sectionId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeBuilders($query)
    {
        return $query->where('is_builder', true);
    }

    public function scopeRegularQuestions($query)
    {
        return $query->where('is_builder', false);
    }

    public function scopeHiddenForBankflip($query)
    {
        return $query->where('hide_if_bankflip_filled', true);
    }

    public function scopeVisibleForBankflip($query)
    {
        return $query->where('hide_if_bankflip_filled', false);
    }

    public function hasCondition(): bool
    {
        return ! empty($this->condition);
    }

    public function hasRequiredCondition(): bool
    {
        return ! empty($this->required_condition);
    }

    public function hasOptionalCondition(): bool
    {
        return ! empty($this->optional_condition);
    }

    public function shouldHideForBankflip(): bool
    {
        return $this->hide_if_bankflip_filled;
    }

    public function shouldBlockForBankflip(): bool
    {
        return $this->block_if_bankflip_filled;
    }

    public function shouldBeVisible(array $answers = []): bool
    {
        if (! $this->hasCondition()) {
            return true;
        }

        $condition = $this->condition;
        $dependsOnQuestionId = $condition['dependsOnQuestionId'] ?? null;
        $conditionType = $condition['conditionType'] ?? null;
        $expectedValue = $condition['expectedValue'] ?? null;

        if (! $dependsOnQuestionId || ! $conditionType) {
            return true;
        }

        $answer = $answers[$dependsOnQuestionId] ?? null;
        if ($answer === null) {
            return false;
        }

        return $this->evaluateCondition($answer, $conditionType, $expectedValue);
    }

    public function isRequired(array $answers = []): bool
    {
        if ($this->hasRequiredCondition()) {
            $condition = $this->required_condition;
            $dependsOnQuestionId = $condition['dependsOnQuestionId'] ?? null;
            $conditionType = $condition['conditionType'] ?? null;
            $expectedValue = $condition['expectedValue'] ?? null;

            if ($dependsOnQuestionId && $conditionType) {
                $answer = $answers[$dependsOnQuestionId] ?? null;
                if ($answer !== null) {
                    return $this->evaluateCondition($answer, $conditionType, $expectedValue);
                }
            }
        }

        if ($this->hasOptionalCondition()) {
            $condition = $this->optional_condition;
            $dependsOnQuestionId = $condition['dependsOnQuestionId'] ?? null;
            $conditionType = $condition['conditionType'] ?? null;
            $expectedValue = $condition['expectedValue'] ?? null;

            if ($dependsOnQuestionId && $conditionType) {
                $answer = $answers[$dependsOnQuestionId] ?? null;
                if ($answer !== null) {
                    return ! $this->evaluateCondition($answer, $conditionType, $expectedValue);
                }
            }
        }

        return true;
    }

    private function evaluateCondition($answer, string $conditionType, $expectedValue): bool
    {
        switch ($conditionType) {
            case 'equals':
                return $answer == $expectedValue;
            case 'not_equals':
                return $answer != $expectedValue;
            case 'contains':
                return str_contains($answer, $expectedValue);
            case 'not_contains':
                return ! str_contains($answer, $expectedValue);
            case 'greater_than':
                return $answer > $expectedValue;
            case 'less_than':
                return $answer < $expectedValue;
            case 'greater_than_or_equal':
                return $answer >= $expectedValue;
            case 'less_than_or_equal':
                return $answer <= $expectedValue;
            case 'is_empty':
                return empty($answer);
            case 'is_not_empty':
                return ! empty($answer);
            case 'is_true':
                return $answer === true || $answer === '1' || $answer === 'true';
            case 'is_false':
                return $answer === false || $answer === '0' || $answer === 'false';
            case 'age_less_than':
                return $this->calculateAge($answer) < $expectedValue;
            case 'age_greater_than':
                return $this->calculateAge($answer) > $expectedValue;
            case 'age_between':
                $age = $this->calculateAge($answer);

                return $age >= $expectedValue && $age <= ($condition['expectedValue2'] ?? $expectedValue);
            case 'date_before':
                return strtotime($answer) < strtotime($expectedValue);
            case 'date_after':
                return strtotime($answer) > strtotime($expectedValue);
            case 'date_between':
                $date = strtotime($answer);

                return $date >= strtotime($expectedValue) && $date <= strtotime($condition['expectedValue2'] ?? $expectedValue);
            case 'is_today':
                return date('Y-m-d', strtotime($answer)) === date('Y-m-d');
            case 'is_this_year':
                return date('Y', strtotime($answer)) === date('Y');
            case 'is_this_month':
                return date('Y-m', strtotime($answer)) === date('Y-m');
            default:
                return false;
        }
    }

    private function calculateAge($birthDate): int
    {
        if (is_string($birthDate)) {
            $birthDate = strtotime($birthDate);
        }

        if (! $birthDate) {
            return 0;
        }

        return date('Y') - date('Y', $birthDate) - (date('md') < date('md', $birthDate) ? 1 : 0);
    }
}
