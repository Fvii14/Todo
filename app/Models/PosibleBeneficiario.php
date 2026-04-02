<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosibleBeneficiario extends Model
{
    protected $table = 'posibles_beneficiarios';

    protected $fillable = [
        'ayuda_id',
        'user_id',
        'nombre_completo',
        'email',
        'telefono',
        'ccaa',
    ];

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
