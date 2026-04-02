<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratacionPagos extends Model
{
    use HasFactory;

    protected $table = 'contratacion_pagos';

    protected $fillable = [
        'contratacion_id',
        'payment_id',
    ];

    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }
}
