<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaSolicitada extends Model
{
    protected $table = 'ayudas_solicitadas';

    protected $fillable = [
        'user_id',
        'ayuda_id',
        'estado',
        'fecha_solicitud',
        'observaciones',
        'motivo_rechazo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }

    protected $casts = [
        'fecha_solicitud' => 'datetime',
    ];

    public function tramite()
    {
        return $this->hasOne(Contratacion::class, 'ayuda_id', 'ayuda_id');
    }
}
