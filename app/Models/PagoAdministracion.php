<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoAdministracion extends Model
{
    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }

    public function cobros()
    {
        return $this->hasMany(CobroTtf::class, 'pago_admin_id');
    }
}
