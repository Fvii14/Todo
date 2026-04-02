<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wizard extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'user_id',
        'data',
        'current_step',
        'status',
        'title',
        'description',
        'duplicated_from_id',
        'duplication_reason',
        'duplicated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'current_step' => 'integer',
        'duplicated_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';

    const STATUS_IN_REVIEW = 'in_review';

    const STATUS_COMPLETED = 'completed';

    const TYPE_AYUDA = 'ayuda';

    const TYPE_COLLECTOR = 'collector';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDataValue(string $key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    public function setDataValue(string $key, $value): void
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->data = $data;
    }

    public function nextStep(): void
    {
        $this->current_step++;
        $this->save();
    }

    public function previousStep(): void
    {
        if ($this->current_step > 1) {
            $this->current_step--;
            $this->save();
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1) {
            $this->current_step = $step;
            $this->save();
        }
    }

    public function markAsCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    public function markAsInReview(): void
    {
        $this->status = self::STATUS_IN_REVIEW;
        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isInReview(): bool
    {
        return $this->status === self::STATUS_IN_REVIEW;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function convivienteTypes()
    {
        return $this->hasMany(ConvivienteType::class)->orderBy('order');
    }

    public function onboarders()
    {
        return $this->hasMany(Onboarder::class);
    }

    public function duplicatedFrom(): BelongsTo
    {
        return $this->belongsTo(Wizard::class, 'duplicated_from_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(Wizard::class, 'duplicated_from_id');
    }

    public function isDuplicate(): bool
    {
        return ! is_null($this->duplicated_from_id);
    }

    public function getOriginalWizard(): ?Wizard
    {
        return $this->duplicatedFrom;
    }
}
