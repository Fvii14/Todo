<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'nota',
        'fecha_alerta',
        'activa',
    ];

    protected $casts = [
        'fecha_alerta' => 'datetime',
        'activa' => 'boolean',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para alertas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para alertas por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_alerta', $fecha);
    }

    /**
     * Scope para alertas próximas (próximas 7 días)
     */
    public function scopeProximas($query)
    {
        return $query->where('fecha_alerta', '>=', now())
            ->where('fecha_alerta', '<=', now()->addDays(7))
            ->where('activa', true);
    }

    /**
     * Scope para alertas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_alerta', '<', now())
            ->where('activa', true);
    }

    /**
     * Verificar si la alerta está vencida
     */
    public function isVencida()
    {
        return $this->fecha_alerta < now();
    }

    /**
     * Verificar si la alerta es próxima (próximas 24 horas)
     */
    public function isProxima()
    {
        return $this->fecha_alerta >= now() && $this->fecha_alerta <= now()->addDay();
    }

    /**
     * Formatear fecha de alerta
     */
    public function getFechaAlertaFormateadaAttribute()
    {
        return $this->fecha_alerta->format('d/m/Y H:i');
    }

    /**
     * Obtener tiempo restante hasta la alerta
     */
    public function getTiempoRestanteAttribute()
    {
        $now = now();
        $fecha = $this->fecha_alerta;

        if ($fecha < $now) {
            return 'Vencida';
        }

        $diff = $now->diff($fecha);

        if ($diff->days > 0) {
            return $diff->days.' día'.($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h.' hora'.($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i.' minuto'.($diff->i > 1 ? 's' : '');
        }
    }
}
