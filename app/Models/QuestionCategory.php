<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación many-to-many con preguntas
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_category_relations');
    }

    /**
     * Relación con la categoría padre
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'parent_id');
    }

    /**
     * Relación con las categorías hijas
     */
    public function children(): HasMany
    {
        return $this->hasMany(QuestionCategory::class, 'parent_id');
    }

    /**
     * Obtener todas las categorías hijas recursivamente
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener categorías ordenadas por nombre
     */
    public static function getOrdered()
    {
        return static::active()->orderBy('name')->get();
    }

    /**
     * Obtener solo categorías padre (sin parent_id)
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Obtener solo subcategorías (con parent_id)
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Verificar si es una categoría padre
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Verificar si es una subcategoría
     */
    public function isChild(): bool
    {
        return ! is_null($this->parent_id);
    }

    /**
     * Obtener el nombre completo con jerarquía (ej: "Padre > Hijo")
     */
    public function getFullNameAttribute(): string
    {
        if ($this->isParent()) {
            return $this->name;
        }

        return $this->parent ? $this->parent->name.' > '.$this->name : $this->name;
    }

    /**
     * Obtener todas las categorías con jerarquía para selects
     */
    public static function getHierarchical()
    {
        $parents = static::active()->parents()->orderBy('name')->get();

        $result = [];
        foreach ($parents as $parent) {
            $result[] = static::buildHierarchicalCategory($parent);
        }

        return $result;
    }

    /**
     * Construir recursivamente una categoría con todos sus descendientes
     */
    private static function buildHierarchicalCategory($category)
    {
        $children = static::active()->where('parent_id', $category->id)->orderBy('name')->get();

        $categoryData = [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'is_active' => $category->is_active,
            'parent_id' => $category->parent_id,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
            'is_parent' => $children->isNotEmpty(),
            'children' => [],
        ];

        foreach ($children as $child) {
            $categoryData['children'][] = static::buildHierarchicalCategory($child);
        }

        return $categoryData;
    }
}
