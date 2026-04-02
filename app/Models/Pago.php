<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos (si no sigue la convención de nombres de Laravel)
    protected $table = 'pagos';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'contratacion_id',
        'monto',
        'estado',
        'respuesta_stripe',
        'fecha_pago',
    ];

    // Relación con el modelo Contratacion
    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }
}
