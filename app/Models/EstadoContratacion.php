<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EstadoContratacion extends Model
{
    protected $table = 'estados_contratacion';

    protected $fillable = [
        'codigo',
        'grupo',
    ];

    /**
     * Contrataciones que tienen asociado este estado OPx.
     */
    public function contrataciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Contratacion::class,
            'contratacion_estado_contratacion'
        );
    }

    /**
     * Scope para filtrar por grupo (OP1, OP2, OP3, OP4, OP5...).
     */
    public function scopeGrupo($query, string $grupo)
    {
        return $query->where('grupo', $grupo);
    }
}
