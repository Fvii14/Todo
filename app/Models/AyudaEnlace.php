<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaEnlace extends Model
{
    protected $table = 'ayuda_enlaces';

    protected $fillable = [
        'ayuda_id',
        'texto_boton',
        'url',
        'orden',
    ];

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }
}
