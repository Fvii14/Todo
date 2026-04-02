<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'color',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Relación con Products a través de la tabla pivote
     */
    public function productos()
    {
        return $this->belongsToMany(Product::class, 'producto_servicio', 'servicio_id', 'product_id')
            ->withPivot('orden')
            ->withTimestamps()
            ->orderByPivot('orden');
    }
}
