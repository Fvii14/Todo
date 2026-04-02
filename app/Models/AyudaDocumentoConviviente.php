<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaDocumentoConviviente extends Model
{
    protected $table = 'ayuda_documentos_convivientes';

    protected $fillable = [
        'ayuda_id',
        'documento_id',
        'es_obligatorio',
        'conditions',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'conditions' => 'array',
    ];

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }

    public function documento()
    {
        return $this->belongsTo(Document::class, 'documento_id');
    }
}
