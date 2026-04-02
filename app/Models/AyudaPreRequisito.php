<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AyudaPreRequisito extends Model
{
    protected $table = 'ayuda_pre_requisitos';

    protected $fillable = [
        'ayuda_id',
        'name',
        'description',
        'type',
        'target_type',
        'target_conviviente_type',
        'conviviente_type',
        'question_id',
        'operator',
        'value',
        'value2',
        'value_type',
        'age_unit',
        'group_logic',
        'is_required',
        'error_message',
        'order',
        'active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'active' => 'boolean',
        'value' => 'array',
        'value2' => 'array',
        'order' => 'integer',
    ];

    const TYPE_SIMPLE = 'simple';

    const TYPE_GROUP = 'group';

    const TYPE_COMPLEX = 'complex';

    const TARGET_SOLICITANTE = 'solicitante';

    const TARGET_CONVIVIENTE = 'conviviente';

    const TARGET_UNIDAD_CONVIVENCIA_COMPLETA = 'unidad_convivencia_completa';

    const TARGET_UNIDAD_CONVIVENCIA_SIN_SOLICITANTE = 'unidad_convivencia_sin_solicitante';

    const TARGET_UNIDAD_FAMILIAR_COMPLETA = 'unidad_familiar_completa';

    const TARGET_UNIDAD_FAMILIAR_SIN_SOLICITANTE = 'unidad_familiar_sin_solicitante';

    const TARGET_ANY_CONVIVIENTE = 'any_conviviente';

    const TARGET_ANY_FAMILIAR = 'any_familiar';

    const TARGET_ANY_PERSONA_UNIDAD = 'any_persona_unidad';

    const OPERATOR_EQUALS = '==';

    const OPERATOR_NOT_EQUALS = '!=';

    const OPERATOR_GREATER_THAN = '>';

    const OPERATOR_GREATER_EQUAL = '>=';

    const OPERATOR_LESS_THAN = '<';

    const OPERATOR_LESS_EQUAL = '<=';

    const OPERATOR_CONTAINS = 'contains';

    const OPERATOR_NOT_CONTAINS = 'not_contains';

    const OPERATOR_BETWEEN = 'between';

    const OPERATOR_IN = 'in';

    const OPERATOR_NOT_IN = 'not_in';

    const OPERATOR_EXISTS = 'exists';

    const OPERATOR_NOT_EXISTS = 'not_exists';

    const GROUP_LOGIC_AND = 'AND';

    const GROUP_LOGIC_OR = 'OR';

    public function ayuda(): BelongsTo
    {
        return $this->belongsTo(Ayuda::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function groupRules(): HasMany
    {
        return $this->hasMany(AyudaPreRequisitoRule::class, 'pre_requisito_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeForTarget($query, string $targetType)
    {
        return $query->where('target_type', $targetType);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function isSimple(): bool
    {
        return $this->type === self::TYPE_SIMPLE;
    }

    public function isGroup(): bool
    {
        return $this->type === self::TYPE_GROUP;
    }

    public function isComplex(): bool
    {
        return $this->type === self::TYPE_COMPLEX;
    }

    public static function getOperators(): array
    {
        return [
            self::OPERATOR_EQUALS => 'Igual a',
            self::OPERATOR_NOT_EQUALS => 'Distinto de',
            self::OPERATOR_GREATER_THAN => 'Mayor que',
            self::OPERATOR_GREATER_EQUAL => 'Mayor o igual que',
            self::OPERATOR_LESS_THAN => 'Menor que',
            self::OPERATOR_LESS_EQUAL => 'Menor o igual que',
            self::OPERATOR_CONTAINS => 'Contiene',
            self::OPERATOR_NOT_CONTAINS => 'No contiene',
            self::OPERATOR_BETWEEN => 'Entre',
            self::OPERATOR_IN => 'En la lista',
            self::OPERATOR_NOT_IN => 'No en la lista',
            self::OPERATOR_EXISTS => 'Existe',
            self::OPERATOR_NOT_EXISTS => 'No existe',
        ];
    }

    public static function getTargetTypes(): array
    {
        return [
            self::TARGET_SOLICITANTE => 'Solicitante',
            self::TARGET_CONVIVIENTE => 'Conviviente específico',
            self::TARGET_UNIDAD_CONVIVENCIA_COMPLETA => 'Unidad de convivencia completa (con solicitante)',
            self::TARGET_UNIDAD_CONVIVENCIA_SIN_SOLICITANTE => 'Unidad de convivencia sin solicitante (solo convivientes)',
            self::TARGET_UNIDAD_FAMILIAR_COMPLETA => 'Unidad familiar completa (con solicitante + solo familiares)',
            self::TARGET_UNIDAD_FAMILIAR_SIN_SOLICITANTE => 'Unidad familiar sin solicitante (solo familiares)',
            self::TARGET_ANY_CONVIVIENTE => 'Cualquier conviviente',
            self::TARGET_ANY_FAMILIAR => 'Cualquier familiar (excluyendo no_familiar)',
            self::TARGET_ANY_PERSONA_UNIDAD => 'Cualquier persona de la unidad',
        ];
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_SIMPLE => 'Requisito simple',
            self::TYPE_GROUP => 'Grupo de requisitos',
            self::TYPE_COMPLEX => 'Requisito complejo',
        ];
    }

    public static function getGroupLogics(): array
    {
        return [
            self::GROUP_LOGIC_AND => 'TODOS deben cumplirse',
            self::GROUP_LOGIC_OR => 'AL MENOS UNO debe cumplirse',
        ];
    }

    public function getFormattedDescription(): string
    {
        if ($this->isSimple()) {
            return $this->getSimpleDescription();
        } elseif ($this->isGroup()) {
            return $this->getGroupDescription();
        } else {
            return $this->getComplexDescription();
        }
    }

    private function getSimpleDescription(): string
    {
        $questionText = $this->question ? $this->question->text : 'Pregunta no encontrada';
        $operatorText = self::getOperators()[$this->operator] ?? $this->operator;
        $valueText = $this->formatValue($this->value);

        return "{$questionText} {$operatorText} {$valueText}";
    }

    private function getGroupDescription(): string
    {
        $logicText = self::getGroupLogics()[$this->group_logic] ?? $this->group_logic;
        $rulesCount = $this->groupRules()->count();

        return "Grupo de {$rulesCount} requisitos ({$logicText})";
    }

    private function getComplexDescription(): string
    {
        return 'Requisito complejo con múltiples condiciones';
    }

    private function formatValue($value): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }

        if ($this->question && $this->question->type === 'boolean') {
            return $value ? 'Sí' : 'No';
        }

        return (string) $value;
    }

    public function getTargetTypeText(): string
    {
        return self::getTargetTypes()[$this->target_type] ?? $this->target_type;
    }

    public function getOperatorText(): string
    {
        return self::getOperators()[$this->operator] ?? $this->operator;
    }
}
