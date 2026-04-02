<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transicion extends Model
{
    use HasFactory;

    protected $table = 'transiciones';

    protected $fillable = [
        'estado_origen',
        'estado_destino',
        'fase_origen',
        'fase_destino',
        'tipo',
        'descripcion',
        'ayuda_id',
    ];

    /**
     * Relación con el estado de origen
     */
    public function estadoOrigen()
    {
        return $this->belongsTo(Estado::class, 'estado_origen', 'slug');
    }

    /**
     * Relación con el estado de destino
     */
    public function estadoDestino()
    {
        return $this->belongsTo(Estado::class, 'estado_destino', 'slug');
    }

    /**
     * Relación con la fase de origen
     */
    public function faseOrigen()
    {
        return $this->belongsTo(Fase::class, 'fase_origen', 'slug');
    }

    /**
     * Relación con la fase de destino
     */
    public function faseDestino()
    {
        return $this->belongsTo(Fase::class, 'fase_destino', 'slug');
    }

    /**
     * Relación con la ayuda
     */
    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class, 'ayuda_id');
    }

    /**
     * Scope para obtener transiciones desde un estado específico
     */
    public function scopeDesdeEstado($query, string $estadoSlug)
    {
        return $query->where('estado_origen', $estadoSlug);
    }

    /**
     * Scope para obtener transiciones hacia un estado específico
     */
    public function scopeHaciaEstado($query, string $estadoSlug)
    {
        return $query->where('estado_destino', $estadoSlug);
    }

    /**
     * Scope para obtener transiciones desde una fase específica
     */
    public function scopeDesdeFase($query, string $faseSlug)
    {
        return $query->where('fase_origen', $faseSlug);
    }

    /**
     * Scope para obtener transiciones hacia una fase específica
     */
    public function scopeHaciaFase($query, string $faseSlug)
    {
        return $query->where('fase_destino', $faseSlug);
    }

    /**
     * Scope para obtener transiciones por tipo
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para obtener transiciones por ayuda
     */
    public function scopePorAyuda($query, int $ayudaId)
    {
        return $query->where('ayuda_id', $ayudaId);
    }

    /**
     * Método estático para obtener las próximas transiciones disponibles desde un estado y fase
     */
    public static function getProximasTransiciones(string $estadoSlug, ?string $faseSlug = null, ?int $ayudaId = null)
    {
        $query = self::query();

        // Filtrar por ayuda si se especifica
        if ($ayudaId) {
            $query->where('ayuda_id', $ayudaId);
        }

        // Buscar transiciones que coincidan con el estado origen
        $query->where('estado_origen', $estadoSlug);

        // Si hay fase, buscar transiciones que incluyan esa fase específica
        if ($faseSlug !== null) {
            $query->where(function ($q) use ($faseSlug) {
                $q->where('fase_origen', $faseSlug)
                    ->orWhere('fase_origen', null); // También incluir transiciones sin fase origen
            });
        } else {
            // Si no hay fase, buscar transiciones sin fase origen o que permitan fase null
            $query->where(function ($q) {
                $q->where('fase_origen', null)
                    ->orWhere('tipo', 'estado'); // Transiciones que solo cambian estado
            });
        }

        return $query->with(['estadoDestino', 'faseDestino', 'ayuda'])->get();
    }

    /**
     * Método estático para verificar si una transición es válida
     */
    public static function esTransicionValida(string $estadoOrigen, ?string $faseOrigen, string $estadoDestino, ?string $faseDestino): bool
    {
        $query = self::where('estado_origen', $estadoOrigen)
            ->where('estado_destino', $estadoDestino);

        // Determinar el tipo de transición basado en las fases
        if ($faseOrigen !== null && $faseDestino !== null) {
            // Transición que cambia tanto estado como fase
            $query->where('fase_origen', $faseOrigen)
                ->where('fase_destino', $faseDestino)
                ->where('tipo', 'ambos');
        } elseif ($faseOrigen !== null && $faseDestino === null) {
            // Transición que solo cambia el estado (de fase a sin fase)
            $query->where('fase_origen', $faseOrigen)
                ->where('fase_destino', null)
                ->where('tipo', 'estado');
        } elseif ($faseOrigen === null && $faseDestino !== null) {
            // Transición que cambia de sin fase a fase (puede ser tipo 'estado' o 'ambos')
            $query->where('fase_origen', null)
                ->where('fase_destino', $faseDestino)
                ->whereIn('tipo', ['estado', 'ambos']);
        } else {
            // Solo cambio de estado (sin fases)
            $query->where('fase_origen', null)
                ->where('fase_destino', null)
                ->where('tipo', 'estado');
        }

        return $query->exists();
    }

    /**
     * Método para obtener solo los próximos estados disponibles
     */
    public static function getProximosEstados(string $estadoSlug, ?string $faseSlug = null)
    {
        $transiciones = self::getProximasTransiciones($estadoSlug, $faseSlug);

        return $transiciones->filter(function ($transicion) {
            return $transicion->tipo === 'estado' || $transicion->tipo === 'ambos';
        })->pluck('estadoDestino')->unique();
    }

    /**
     * Método para obtener solo las próximas fases disponibles
     */
    public static function getProximasFases(string $estadoSlug, ?string $faseSlug = null)
    {
        $transiciones = self::getProximasTransiciones($estadoSlug, $faseSlug);

        return $transiciones->filter(function ($transicion) {
            return $transicion->tipo === 'fase' || $transicion->tipo === 'ambos';
        })->pluck('faseDestino')->unique();
    }
}
