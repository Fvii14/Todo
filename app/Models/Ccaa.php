<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ccaa extends Model
{
    // Nombre exacto de la tabla
    protected $table = 'ccaa';

    // Si no usas timestamps en esta tabla (created_at, updated_at)
    public $timestamps = false;

    // Si necesitas proteger los campos contra asignación masiva, indícalos aquí
    protected $fillable = [
        'nombre_ccaa',
    ];

    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'id_ccaa');
    }
}
