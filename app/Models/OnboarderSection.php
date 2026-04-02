<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboarderSection extends Model
{
    protected $fillable = [
        'onboarder_id',
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

    public function onboarder(): BelongsTo
    {
        return $this->belongsTo(Onboarder::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(OnboarderQuestion::class)->orderBy('order');
    }

    public function convivienteTypes(): BelongsToMany
    {
        return $this->belongsToMany(ConvivienteType::class, 'conviviente_type_sections', 'onboarder_section_id', 'conviviente_type_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(OnboarderMetric::class, 'section_id');
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
            default:
                return false;
        }
    }

    public function getQuestionsCount(): int
    {
        return $this->questions()->count();
    }
}
