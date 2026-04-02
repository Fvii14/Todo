<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AyudaRequisitoVersion extends Model
{
    protected $table = 'ayuda_requisitos_versions';

    protected $fillable = [
        'ayuda_id',
        'version_number',
        'json_regla',
        'descripcion',
        'is_active',
        'is_draft',
        'created_by',
        'published_at',
        'version_description',
    ];

    protected $casts = [
        'json_regla' => 'array',
        'is_active' => 'boolean',
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function ayuda(): BelongsTo
    {
        return $this->belongsTo(Ayuda::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getActiveVersion($ayudaId)
    {
        return self::where('ayuda_id', $ayudaId)
            ->where('is_active', true)
            ->first();
    }

    public static function getCurrentDraft($ayudaId)
    {
        return self::where('ayuda_id', $ayudaId)
            ->where('is_draft', true)
            ->orderBy('version_number', 'desc')
            ->first();
    }

    public static function getNextVersionNumber($ayudaId)
    {
        $maxVersion = self::where('ayuda_id', $ayudaId)->max('version_number');

        return ($maxVersion ?? 0) + 1;
    }

    public function publish()
    {
        self::where('ayuda_id', $this->ayuda_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->update([
            'is_active' => true,
            'is_draft' => false,
            'published_at' => now(),
        ]);
    }

    public function createDraft()
    {
        return self::create([
            'ayuda_id' => $this->ayuda_id,
            'version_number' => self::getNextVersionNumber($this->ayuda_id),
            'json_regla' => $this->json_regla,
            'descripcion' => $this->descripcion,
            'is_active' => false,
            'is_draft' => true,
            'created_by' => Auth::user()->id ?? 1,
            'version_description' => 'Draft basado en versión '.$this->version_number,
        ]);
    }
}
