<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AyudaRecurso extends Pivot
{
    use HasFactory;

    protected $table = 'ayuda_recurso';

    protected $fillable = [
        'ayuda_id',
        'recurso_id',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }

    public function recurso()
    {
        return $this->belongsTo(Recurso::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden', 'asc');
    }
}
