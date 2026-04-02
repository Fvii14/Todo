<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class AyudaRequisitoJson extends Model
{
    protected $table = 'ayuda_requisitos_json';

    protected $fillable = [
        'ayuda_id',
        'descripcion',
        'json_regla',
    ];

    protected $casts = [
        'json_regla' => 'array',
    ];

    public function ayuda(): BelongsTo
    {
        return $this->belongsTo(Ayuda::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AyudaRequisitoVersion::class, 'ayuda_id', 'ayuda_id');
    }

    public function getActiveVersion()
    {
        return AyudaRequisitoVersion::getActiveVersion($this->ayuda_id);
    }

    public function getCurrentDraft()
    {
        return AyudaRequisitoVersion::getCurrentDraft($this->ayuda_id);
    }

    public function createDraft($description = null)
    {
        return AyudaRequisitoVersion::create([
            'ayuda_id' => $this->ayuda_id,
            'version_number' => AyudaRequisitoVersion::getNextVersionNumber($this->ayuda_id),
            'json_regla' => $this->json_regla,
            'descripcion' => $this->descripcion,
            'is_active' => false,
            'is_draft' => true,
            'created_by' => Auth::user()->id ?? 1,
            'version_description' => $description ?? 'Nuevo draft',
        ]);
    }
}
