<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyudaDocumento extends Model
{
    protected $table = 'ayuda_documentos';

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
