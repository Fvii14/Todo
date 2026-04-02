<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivoSubsanacionContratacion extends Model
{
    use HasFactory;

    protected $table = 'motivos_subsanacion_contrataciones';

    protected $fillable = [
        'contratacion_id',
        'motivo_id',
        'estado_subsanacion',
        'nota',
    ];

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_COMPLETADA = 'completada';

    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }

    public function motivo()
    {
        return $this->belongsTo(MotivoSubsanacionAyuda::class, 'motivo_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_subsanacion', self::ESTADO_PENDIENTE);
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado_subsanacion', self::ESTADO_COMPLETADA);
    }

    public function marcarComoCompletada(): void
    {
        $this->estado_subsanacion = self::ESTADO_COMPLETADA;
        $this->save();
    }
}
