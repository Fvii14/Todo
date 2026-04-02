<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url_error',
        'navegador',
        'version_navegador',
        'so',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Estados disponibles
    const ESTADO_PENDIENTE = 'pendiente';

    const ESTADO_EN_REVISION = 'en_revision';

    const ESTADO_RESUELTO = 'resuelto';

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope para filtrar por estado
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    // Scope para tickets pendientes
    public function scopePendientes($query)
    {
        return $query->porEstado(self::ESTADO_PENDIENTE);
    }

    // Scope para tickets en revisión
    public function scopeEnRevision($query)
    {
        return $query->porEstado(self::ESTADO_EN_REVISION);
    }

    // Scope para tickets resueltos
    public function scopeResueltos($query)
    {
        return $query->porEstado(self::ESTADO_RESUELTO);
    }

    // Método para cambiar estado
    public function cambiarEstado($nuevoEstado)
    {
        $estadosValidos = [self::ESTADO_PENDIENTE, self::ESTADO_EN_REVISION, self::ESTADO_RESUELTO];

        if (in_array($nuevoEstado, $estadosValidos)) {
            $this->update(['estado' => $nuevoEstado]);

            return true;
        }

        return false;
    }

    // Método para obtener el estado en español
    public function getEstadoTextoAttribute()
    {
        $estados = [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EN_REVISION => 'En Revisión',
            self::ESTADO_RESUELTO => 'Resuelto',
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    // Método para obtener la clase CSS del estado
    public function getEstadoClaseAttribute()
    {
        $clases = [
            self::ESTADO_PENDIENTE => 'badge bg-warning',
            self::ESTADO_EN_REVISION => 'badge bg-info',
            self::ESTADO_RESUELTO => 'badge bg-success',
        ];

        return $clases[$this->estado] ?? 'badge bg-secondary';
    }
}
