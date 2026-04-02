<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserConviviente extends Model
{
    protected $fillable = [
        'onboarder_id',
        'conviviente_type_id',
        'data',
        'order',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function onboarder(): BelongsTo
    {
        return $this->belongsTo(Onboarder::class);
    }

    public function convivienteType(): BelongsTo
    {
        return $this->belongsTo(ConvivienteType::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function scopeForOnboarder($query, int $onboarderId)
    {
        return $query->where('onboarder_id', $onboarderId);
    }

    public function scopeForConvivienteType($query, int $convivienteTypeId)
    {
        return $query->where('conviviente_type_id', $convivienteTypeId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getDisplayName(): string
    {
        return $this->convivienteType->getDisplayName();
    }

    public function getIconClass(): string
    {
        return $this->convivienteType->getIconClass();
    }

    public function getAnswersCount(): int
    {
        return $this->answers()->count();
    }

    public function getDataValue(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function setDataValue(string $key, $value): void
    {
        $data = $this->data ?? [];
        $data[$key] = $value;
        $this->data = $data;
        $this->save();
    }
}
