<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaContratacion extends Model
{
    protected $table = 'notas_contrataciones';

    public $timestamps = false;

    protected $fillable = ['nota', 'user_id', 'tramitador_id', 'contratacion_id', 'destacada', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'destacada' => 'boolean',
    ];

    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }

    public function tramitador()
    {
        return $this->belongsTo(User::class, 'tramitador_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
