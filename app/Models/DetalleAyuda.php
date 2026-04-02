<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleAyuda extends Model
{
    use HasFactory;

    protected $table = 'detalles_ayuda';

    public $timestamps = false;

    public function getPresupuestoFormateadoAttribute()
    {
        return number_format($this->presupuesto ?? 0, 0, ',', '.').'€';
    }

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'id_ayuda');
    }
}
