<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaDatoCondition extends Model
{
    protected $fillable = [
        'ayuda_dato_id', 'question_slug', 'operador', 'valor',
    ];

    public function ayudaDato()
    {
        return $this->belongsTo(AyudaDato::class);
    }
}
