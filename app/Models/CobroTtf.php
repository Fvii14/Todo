<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CobroTtf extends Model
{
    public function pagoAdministracion()
    {
        return $this->belongsTo(PagoAdministracion::class, 'pago_admin_id');
    }

    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }
}
