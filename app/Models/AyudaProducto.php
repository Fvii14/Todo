<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyudaProducto extends Model
{
    use HasFactory;

    protected $table = 'ayuda_producto';

    protected $fillable = [
        'ayuda_id',
        'product_id',
        'recomendado',
    ];

    protected $casts = [
        'recomendado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Relación con Ayuda
     */
    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'ayuda_id');
    }

    /**
     * Relación con Products
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
