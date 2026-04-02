<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubsanacionDocument extends Model
{
    protected $table = 'subsanacion_documents';

    protected $fillable = [
        'contratacion_id',
        'document_id',
        'solicitado_por',
        'estado',             // pendiente, subido, validado, rechazado
        'motivo_rechazo',     // ilegible, incorrecto, etc.
        'nota_personalizada', // cuando motivo_rechazo = personalizado
        'fecha_solicitado',
        'fecha_completado',
    ];

    /**
     * Relación: el documento de subsanación pertenece a una contratación.
     */
    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class, 'contratacion_id');
    }

    /**
     * Relación: el documento de subsanación está asociado a un tipo de documento.
     */
    public function documento()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Relación: quién solicitó este documento (usuario del equipo de operativa).
     */
    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
