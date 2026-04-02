<?php

// app/Models/Municipio.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $table = 'municipio'; // por si Laravel no lo infiere automáticamente

    protected $fillable = ['nombre_municipio', 'provincia_id']; // ajusta según tus columnas
}
