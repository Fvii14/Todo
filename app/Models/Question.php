<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $fillable = [
        'slug',
        'text',
        'sub_text',
        'text_conviviente',
        'sub_text_conviviente',
        'type',
        'options',
        'regex_id',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public static $types = [
        'string' => 'Texto',
        'integer' => 'Número',
        'boolean' => 'Sí / No',
        'select' => 'Selección',
        'multiple' => 'Selección múltiple',
        'date' => 'Fecha',
        'info' => 'Informativa',
        'builder' => 'Builder',
    ];

    // Mantener compatibilidad con el código existente
    public static $sectores = [
        'alquiler' => 'Alquiler',
        'familia' => 'Familia',
        'imv' => 'IMV',
        'collector' => 'Collector',
    ];

    public static $categorias = [
        'vivienda' => 'Vivienda',
        'deudas' => 'Deudas',
        'datos-economicos' => 'Datos Económicos',
        'convivientes' => 'Convivientes',
        'grupo-vulnerable' => 'Grupo Vulnerable',
        'datos-personales' => 'Datos Personales',
        'hijos' => 'Hijos',
    ];

    /**
     * Relación con cuestionarios
     */
    public function questionnaires()
    {
        return $this->belongsToMany(Questionnaire::class, 'questionnaire_questions');
    }

    /**
     * Relación con regex
     */
    public function regex(): BelongsTo
    {
        return $this->belongsTo(Regex::class, 'regex_id');
    }

    /**
     * Relación many-to-many con categorías
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category_relations');
    }

    /**
     * Relación many-to-many con finalidades
     */
    public function purposes(): BelongsToMany
    {
        return $this->belongsToMany(QuestionPurpose::class, 'question_purpose_relations', 'question_id', 'question_purpose_id');
    }

    public function category(): BelongsToMany
    {
        return $this->belongsToMany(QuestionCategory::class, 'question_category_relations');
    }

    /**
     * Obtener los nombres de las categorías (compatibilidad)
     */
    public function getCategoriaNamesAttribute()
    {
        if ($this->categories->count() > 0) {
            return $this->categories->pluck('name')->join(', ');
        }

        return 'Sin categorías';
    }

    public function labelForOption(int $index): ?string
    {
        $ops = $this->options ?? [];

        if ($index < 0) {
            foreach ($ops as $label) {
                if (mb_strtolower(trim($label)) === 'ninguna de las anteriores') {
                    return $label;
                }
            }

            return $ops[0] ?? null;
        }

        return $ops[$index] ?? null;
    }

    public function labelsForMultiple(array $indexes): array
    {
        $ops = $this->options ?? [];
        $labels = [];

        foreach ($indexes as $i) {
            if ($i === '-1' || ! is_numeric($i) || (int) $i < 0 || ! isset($ops[(int) $i])) {
                foreach ($ops as $label) {
                    if (mb_strtolower(trim($label)) === 'ninguna de las anteriores') {
                        return [$label];
                    }
                }

                return ['Ninguna de las anteriores'];
            }
        }

        foreach ($indexes as $i) {
            $pos = (int) $i;
            if (isset($ops[$pos])) {
                $labels[] = $ops[$pos];
            }
        }

        return $labels;
    }

    /* ---------- Scopes para consultas reutilizables ---------- */

    /**
     * Scope para filtrar por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope para obtener preguntas de un cuestionario con orden
     */
    public function scopeForQuestionnaire($query, int $questionnaireId)
    {
        return $query->whereIn('questions.id', function ($q) use ($questionnaireId) {
            $q->select('question_id')
                ->from('questionnaire_questions')
                ->where('questionnaire_id', $questionnaireId);
        })
            ->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->where('questionnaire_questions.questionnaire_id', $questionnaireId)
            ->orderBy('questionnaire_questions.orden')
            ->select('questions.*', 'questionnaire_questions.orden');
    }

    /**
     * Scope para obtener IDs de preguntas de un cuestionario (retorna query builder)
     */
    public function scopeQuestionIdsByQuestionnaire($query, int $questionnaireId)
    {
        return $query->join('questionnaire_questions', 'questions.id', '=', 'questionnaire_questions.question_id')
            ->where('questionnaire_questions.questionnaire_id', $questionnaireId)
            ->select('questions.id');
    }

    /**
     * Scope para obtener regex de validación para preguntas
     */
    public function scopeWithRegex($query, array $questionIds)
    {
        return $query->join('regex', 'questions.regex_id', '=', 'regex.id')
            ->whereIn('questions.id', $questionIds)
            ->select('questions.id as question_id', 'regex.pattern', 'regex.error_message');
    }
}
