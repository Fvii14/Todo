<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaRequisito extends Model
{
    protected $table = 'ayuda_requisitos';  // Definir la tabla asociada

    protected $fillable = [
        'ayuda_id',
        'question_id',
        'respuesta_expected',
    ];

    // Definir la relación con el modelo Ayuda
    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }

    // Definir la relación con el modelo Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function getFormattedExpectedAnswerAttribute()
    {
        if ($this->question->type === 'boolean') {
            return $this->respuesta_expected == 1 ? 'Sí' : 'No';
        }

        return $this->respuesta_expected;
    }
}
