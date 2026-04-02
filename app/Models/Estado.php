<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estados';

    protected $fillable = ['nombre', 'slug'];

    /**
     * Obtener todos los estados ordenados por nombre
     */
    public static function getAllOrdered(): Collection
    {
        return static::orderBy('nombre')->get();
    }

    /**
     * Buscar un estado por slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Verificar si un slug existe
     */
    public static function slugExists(string $slug): bool
    {
        return static::where('slug', $slug)->exists();
    }

    /**
     * Obtener todos los slugs de estados
     */
    public static function getAllSlugs(): array
    {
        return static::pluck('slug')->toArray();
    }
}
