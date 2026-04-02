<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AyudaPreRequisitoRule extends Model
{
    protected $table = 'ayuda_pre_requisito_rules';

    protected $fillable = [
        'pre_requisito_id',
        'question_id',
        'operator',
        'value',
        'value2',
        'value_type',
        'age_unit',
        'order',
    ];

    protected $casts = [
        'value' => 'array',
        'value2' => 'array',
        'order' => 'integer',
    ];

    public function preRequisito(): BelongsTo
    {
        return $this->belongsTo(AyudaPreRequisito::class, 'pre_requisito_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getOperatorText(): string
    {
        return AyudaPreRequisito::getOperators()[$this->operator] ?? $this->operator;
    }

    public function formatValue(): string
    {
        if (is_array($this->value)) {
            return implode(', ', $this->value);
        }

        if ($this->question && $this->question->type === 'boolean') {
            return $this->value ? 'Sí' : 'No';
        }

        return (string) $this->value;
    }

    public function getFormattedDescription(): string
    {
        $questionText = $this->question ? $this->question->text : 'Pregunta no encontrada';
        $operatorText = $this->getOperatorText();
        $valueText = $this->formatValue();

        return "{$questionText} {$operatorText} {$valueText}";
    }
}
