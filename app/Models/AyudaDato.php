<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaDato extends Model
{
    protected $table = 'ayuda_datos';

    public $timestamps = false;

    protected $fillable = [
        'ayuda_id',
        'question_slug',
        'tipo_dato',
        'fase',
    ];

    protected $casts = [
        'fase' => 'string',
    ];

    /** Relación con Ayuda */
    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'ayuda_id');
    }

    /** Relación con Question por slug */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_slug', 'slug');
    }

    /** Relación con condiciones */
    public function condiciones()
    {
        return $this->hasMany(AyudaDatoCondition::class, 'ayuda_dato_id');
    }
}
