<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Educacion extends Model
{
    protected $fillable = [
        'user_id',
        'conviviente_id',
        'tipo',
        'institucion',
        'nombre_estudio',
        'nivel',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'provincia_id',
        'municipio_id',
        'ownership',
        'modality',
        'is_official',
        'is_enrolled',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conviviente(): BelongsTo
    {
        return $this->belongsTo(Conviviente::class);
    }
}
