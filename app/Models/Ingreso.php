<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingreso extends Model
{
    protected $table = 'ingresos';

    protected $fillable = [
        'user_id',
        'conviviente_id',
        'tipo',
        'meses',
        'importe_medio',
        'importe_anual',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conviviente(): BelongsTo
    {
        return $this->belongsTo(Conviviente::class);
    }
}
