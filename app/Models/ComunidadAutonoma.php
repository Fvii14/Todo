<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComunidadAutonoma extends Model
{
    protected $table = 'ccaa';

    protected $fillable = ['nombre_ccaa'];

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = false;
}
