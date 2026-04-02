<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organo extends Model
{
    protected $table = 'organos';

    protected $fillable = [
        'id',
        'nombre_organismo',
        'ambito',
        'id_ccaa',
    ];

    public function ayudas()
    {
        return $this->hasMany(Ayuda::class);
    }
}
