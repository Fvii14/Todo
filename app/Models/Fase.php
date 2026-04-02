<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    protected $table = 'fase';

    protected $fillable = ['nombre', 'slug', 'estado'];

    // Relación con Estado vía slug
    public function estadoRef()
    {
        return $this->belongsTo(Estado::class, 'estado', 'slug');
    }
}
