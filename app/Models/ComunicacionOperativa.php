<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComunicacionOperativa extends Model
{
    use HasFactory;

    protected $table = 'comunicaciones_operativa';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'tramitador_id',
        'tipo_comunicacion', // WhatsApp | Email | Llamada
        'fecha_hora',
        'auto',
        'subject',
        'direction',         // in | out
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'auto' => 'boolean',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tramitador()
    {
        return $this->belongsTo(User::class, 'tramitador_id');
    }
}
