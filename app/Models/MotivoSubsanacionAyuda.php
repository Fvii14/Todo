<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivoSubsanacionAyuda extends Model
{
    use HasFactory;

    protected $table = 'motivos_subsanacion_ayuda';

    public const MOTIVO_PADRON = 'Padrón';

    public const MOTIVO_CONTRATO = 'Contrato';

    public const MOTIVO_RECIBOS = 'Recibos';

    protected $fillable = [
        'index',
        'descripcion',
        'ayuda_id',
        'motivo',
        'document_id',
    ];

    protected $casts = [
        'index' => 'integer',
        'ayuda_id' => 'integer',
        'motivo' => 'string',
        'document_id' => 'integer',
    ];

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'ayuda_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function scopeDeAyuda($query, int $ayudaId)
    {
        return $query->where('ayuda_id', $ayudaId);
    }

    public function scopeMotivo($query, string $motivo)
    {
        return $query->where('motivo', $motivo);
    }

    public function scopePadron($query)
    {
        return $query->where('motivo', self::MOTIVO_PADRON);
    }

    public function scopeContrato($query)
    {
        return $query->where('motivo', self::MOTIVO_CONTRATO);
    }

    public function scopeRecibos($query)
    {
        return $query->where('motivo', self::MOTIVO_RECIBOS);
    }
}
