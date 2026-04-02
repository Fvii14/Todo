<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTaxInfo extends Model
{
    use HasFactory;

    protected $table = 'user_tax_info';

    protected $fillable = [
        'user_id',
        'nif',
        'full_name',
        'domicilio_fiscal',
        'fecha_nacimiento',
        'estado_civil',
        'sexo',
        'base_imponible_general',
        'base_imponible_ahorro',
        'certificado_irpf',
        'corriente_pago',
        'sin_deudas',
        'telefono',
        'sin_deudas_ss',
        'esta_trabajando',
        'domicilio',
        'codigo_postal',
        'municipio',
        'provincia',
        'entidad_colectiva',
        'entidad_singular',
        'nucleo_diseminado',
        'fecha_variacion',
        'hizo_collector_real',
        'tiene_propiedad',
        'comunidad_autonoma',
        'deduccion_maternidad',
        'hijos',
    ];

    protected $casts = [
        'hijos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
