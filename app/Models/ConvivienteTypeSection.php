<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConvivienteTypeSection extends Model
{
    protected $fillable = [
        'onboarder_id',
        'conviviente_type_id',
        'name',
        'description',
        'order',
        'skip_condition',
        'is_required',
        'is_skippeable',
    ];

    protected $casts = [
        'skip_condition' => 'array',
        'is_required' => 'boolean',
        'is_skippeable' => 'boolean',
    ];

    public function convivienteType(): BelongsTo
    {
        return $this->belongsTo(ConvivienteType::class);
    }

    public function onboarder(): BelongsTo
    {
        return $this->belongsTo(Onboarder::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ConvivienteTypeQuestion::class)->orderBy('order');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(OnboarderMetric::class, 'section_id');
    }

    public function scopeForConvivienteType($query, int $convivienteTypeId)
    {
        return $query->where('conviviente_type_id', $convivienteTypeId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeSkippeable($query)
    {
        return $query->where('is_skippeable', true);
    }

    public function hasSkipCondition(): bool
    {
        return ! empty($this->skip_condition);
    }

    public function shouldSkip(array $answers = []): bool
    {
        if (! $this->hasSkipCondition()) {
            return false;
        }

        $condition = $this->skip_condition;
        $dependsOnQuestionId = $condition['dependsOnQuestionId'] ?? null;
        $conditionType = $condition['conditionType'] ?? null;
        $expectedValue = $condition['expectedValue'] ?? null;

        if (! $dependsOnQuestionId || ! $conditionType) {
            return false;
        }

        $answer = $answers[$dependsOnQuestionId] ?? null;
        if ($answer === null) {
            return false;
        }

        return $this->evaluateCondition($answer, $conditionType, $expectedValue);
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
            case 'is_empty':
                return empty($answer);
            case 'is_not_empty':
                return ! empty($answer);
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

    public function getQuestionsCount(): int
    {
        return $this->questions()->count();
    }
}
