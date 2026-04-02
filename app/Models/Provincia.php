<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    // Nombre exacto de la tabla en tu base de datos
    protected $table = 'provincia';

    // Si no tienes campos created_at/updated_at en esa tabla:
    public $timestamps = false;

    // Campos que vas a llenar por asignación masiva
    protected $fillable = [
        'codigo_provincia',  // o el nombre que uses
        'id_ccaa',
        'nombre_provincia',
    ];

    /**
     * Relación inversa a CCAA
     */
    public function ccaa()
    {
        return $this->belongsTo(Ccaa::class, 'id_ccaa');
    }
}
