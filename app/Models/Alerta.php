<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';

    protected $fillable = [
        'ayuda_id',
        'contratacion_id',
        'tipo_plazo',
        'fecha_inicio',
        'fecha_fin',
        'tipo_alerta',
        'descripcion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public const TIPO_PLAZO_MENSUAL = 'mensual';

    public const TIPO_PLAZO_PERSONALIZADO = 'personalizado';

    public const TIPO_ALERTA_JUSTIFICACION = 'justificacion';

    public const TIPO_ALERTA_SUBSANACION = 'subsanacion';

    public const TIPO_ALERTA_APERTURA = 'apertura';

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'ayuda_id');
    }

    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class, 'contratacion_id');
    }

    public function scopeDeTipoPlazo($query, string $tipoPlazo)
    {
        return $query->where('tipo_plazo', $tipoPlazo);
    }

    public function scopeDeTipoAlerta($query, string $tipoAlerta)
    {
        return $query->where('tipo_alerta', $tipoAlerta);
    }
}
