<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'allowed_types',
        'informative_clickable_text',
        'informative_header_text',
        'informative_link',
        'informative_link_text',
        'multi_upload', // 👈 añadido
        'tipo',
    ];

    protected $casts = [
        'multi_upload' => 'boolean', // 👈 para que sea true/false
    ];

    public function helps()
    {
        return $this->belongsToMany(Help::class, 'ayuda_documentos')->withPivot('is_required');
    }

    public function userDocuments()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function ayudasConvivientes()
    {
        return $this->belongsToMany(
            Ayuda::class,
            'ayuda_documentos_convivientes',
            'documento_id',
            'ayuda_id'
        )->withPivot('es_obligatorio');
    }
}
