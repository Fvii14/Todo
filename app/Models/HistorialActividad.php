<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialActividad extends Model
{
    protected $table = 'historial_actividad';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'contratacion_id',
        'fecha_inicio',
        'actividad',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime', // o 'immutable_datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class, 'contratacion_id');
    }
}
