<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuestionPurpose extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación many-to-many con las preguntas que tienen esta finalidad
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_purpose_relations', 'question_purpose_id', 'question_id');
    }

    /**
     * Scope para obtener solo finalidades activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener todas las finalidades ordenadas por nombre
     */
    public static function getOrdered()
    {
        return self::orderBy('name')->get();
    }
}
