<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'stripe_product_id',
        'price_id',
        'price',
        'commission_pct',
        'currency',
        'payment_type',

    ];

    public function contrataciones()
    {
        return $this->hasMany(Contratacion::class, 'producto_id');
    }

    /**
     * Relación con AyudaProducto
     */
    public function ayudaProductos()
    {
        return $this->hasMany(AyudaProducto::class, 'product_id');
    }

    /**
     * Relación con Ayuda a través de AyudaProducto
     */
    public function ayudas()
    {
        return $this->belongsToMany(Ayuda::class, 'ayuda_producto', 'product_id', 'ayuda_id');
    }

    /**
     * Relación con Servicios a través de la tabla pivote
     */
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'producto_servicio', 'product_id', 'servicio_id')
            ->withPivot('orden')
            ->withTimestamps()
            ->orderByPivot('orden');
    }
}
