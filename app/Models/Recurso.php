<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    use HasFactory;

    protected $table = 'recursos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'tipo',
        'contenido_texto',
        'url_video',
        'url_imagen',
        'archivo_imagen',
    ];

    public function ayudas()
    {
        return $this->belongsToMany(Ayuda::class, 'ayuda_recurso', 'recurso_id', 'ayuda_id')
            ->withPivot('orden', 'activo')
            ->withTimestamps();
    }

    public function getImagenUrlAttribute()
    {
        if ($this->archivo_imagen) {
            return asset('storage/recursos/'.$this->archivo_imagen);
        }

        return $this->url_imagen;
    }

    public function tieneMultimedia()
    {
        return ! empty($this->url_video) || ! empty($this->url_imagen) || ! empty($this->archivo_imagen);
    }

    public function getContenidoPrincipalAttribute()
    {
        switch ($this->tipo) {
            case 'video':
                return $this->url_video;
            case 'imagen':
                return $this->getImagenUrlAttribute();
            default:
                return $this->contenido_texto;
        }
    }
}
