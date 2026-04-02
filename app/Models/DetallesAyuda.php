<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersTramitesPagos extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_ayuda',
        'Temporalidad',
        'fecha_inicio',
        'fecha_fin',
        'id_organismo',
        'id_ccaa',
        'cantidad_max',
        'año',
        'presupuesto',
        'tipo_persona_bene',
        'contrato_alquiler',
        'documento_identidad',
        'padron',
        'recibos_bancarios',
        'certificado_titularidad_bancaria',
        'certificado_propiedad',
        'nif_fiscal',
    ];

    public function usersTramites()
    {
        return $this->hasMany(UsersTramites::class, 'detalles_ayuda_id');
    }
}
