<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subfase extends Model
{
    protected $table = 'subfase';

    protected $fillable = ['nombre', 'slug', 'fase'];

    public function faseRef()
    {
        return $this->belongsTo(Fase::class, 'fase', 'slug');
    }
}
