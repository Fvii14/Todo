<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratacionDocumentoTramitacion extends Model
{
    use HasFactory;

    protected $table = 'contratacion_documentos_tramitacion';

    protected $fillable = [
        'contratacion_id',
        'slug',
        'nombre_personalizado',
        'orden',
    ];

    protected $casts = [
        'requerido' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Relación con la contratación
     */
    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }

    /**
     * Obtener el documento original basado en el slug
     */
    public function documento()
    {
        return $this->belongsTo(Document::class, 'slug', 'slug');
    }

    /**
     * Obtener el nombre para mostrar (personalizado o original)
     */
    public function getNombreMostrarAttribute()
    {
        return $this->nombre_personalizado ?: $this->documento?->name ?: $this->slug;
    }
}
