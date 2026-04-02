<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';  // Nombre de la tabla en la base de datos

    // Definir los campos que pueden ser asignados masivamente (fillable)
    protected $fillable = [
        'payment_id',
        'status',
        'amount',
        'currency',
        'email',
        'product_id',
    ];

    public function usersTramitesPagos()
    {
        return $this->hasMany(ContratacionPagos::class, 'payment_id');
    }
}
