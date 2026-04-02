<?php

namespace App\Models;

use App\Enums\QuestionnaireTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Questionnaire extends Model
{
    protected $fillable = ['name', 'active', 'redirect_url', 'ayuda_id', 'tipo', 'slug'];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'questionnaire_questions')->withPivot('orden', 'condition', 'next_question_id')->orderBy('questionnaire_questions.orden');
    }

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'ayuda_id');
    }

    public function questionConditions()
    {
        return $this->hasMany(QuestionCondition::class, 'questionnaire_id');
    }

    protected $casts = [
        'tipo' => QuestionnaireTipo::class,
    ];

    /* ---------- Scopes útiles ---------- */
    public function scopeForAyuda($q, int $ayudaId)
    {
        return $q->where('ayuda_id', $ayudaId);
    }

    public function scopeTipoConviviente($q)
    {
        // Soporta enum casteado y valor string, por si en DB está como texto
        return $q->where(function ($qq) {
            $qq->where('tipo', QuestionnaireTipo::CONVIVIENTE)
                ->orWhere('tipo', 'conviviente');
        });
    }

    /* ---------- Helper simple: devuelve las preguntas ---------- */
    public static function convivienteQuestionsByAyuda(int $ayudaId): Collection
    {
        $convQ = static::query()
            ->forAyuda($ayudaId)
            ->tipoConviviente()
            ->with(['questions' => function ($q) {
                $q->select('questions.id', 'questions.slug', 'questions.text', 'questions.type', 'questions.options');
            }])
            ->latest('id')   // si hay varios, coge el último
            ->first();

        return $convQ?->questions ?? collect();
    }

    /* ---------- Variante con caché (opcional) ---------- */
    public static function convivienteQuestionsByAyudaCached(int $ayudaId, int $seconds = 300): Collection
    {
        $key = "qnn_conviviente_questions_ayuda_{$ayudaId}";

        return Cache::remember($key, $seconds, fn () => static::convivienteQuestionsByAyuda($ayudaId));
    }

    /**
     * Obtiene las preguntas necesarias para crear un nuevo conviviente
     * Solo incluye: solo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento y parentesco
     *
     * @param  int  $ayudaId  ID de la ayuda (no se usa, pero se mantiene para compatibilidad)
     * @return array Array con 'questions' (Collection) y 'conditions' (Collection vacía)
     */
    public static function getPreguntasCreacionConviviente(int $ayudaId): array
    {
        $slugs = [
            'solo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'fecha_nacimiento',
            'parentesco',
        ];

        $questions = Question::whereIn('slug', $slugs)
            ->select('id', 'slug', 'text', 'text_conviviente', 'type', 'options', 'sub_text', 'disable_answer')
            ->get()
            ->sortBy(function ($item) use ($slugs) {
                return array_search($item->slug, $slugs);
            })
            ->values();

        return [
            'questions' => $questions,
            'conditions' => collect(), // No se necesitan condiciones para este formulario
        ];
    }

    /* ---------- Scopes adicionales para consultas reutilizables ---------- */

    /**
     * Scope para filtrar por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope para verificar si es cuestionario collector
     */
    public function scopeIsCollector($query)
    {
        $formPostCollector = static::bySlug('form_post_collector')->value('id');
        $formCollector = static::bySlug('form_collector')->value('id');

        return $query->whereIn('id', array_filter([$formPostCollector, $formCollector]));
    }
}
