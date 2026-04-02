<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAyuda extends Model
{
    use HasFactory;

    protected $table = 'user_ayudas';

    protected $fillable = [
        'user_id',
        'ayuda_id',
        'tags',
        'fecha_formulario',
        'estado_comercial',
        'pipeline',
    ];

    protected $casts = [
        'fecha_formulario' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'ayuda_id');
    }
}
