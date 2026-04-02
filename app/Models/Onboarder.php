<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Onboarder extends Model
{
    protected $fillable = [
        'wizard_id',
        'user_id',
        'status',
        'data',
        'started_at',
        'completed_at',
        'current_section_id',
        'current_conviviente_type_id',
    ];

    protected $casts = [
        'data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function wizard(): BelongsTo
    {
        return $this->belongsTo(Wizard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentSection(): BelongsTo
    {
        return $this->belongsTo(OnboarderSection::class, 'current_section_id');
    }

    public function currentConvivienteType(): BelongsTo
    {
        return $this->belongsTo(ConvivienteType::class, 'current_conviviente_type_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(OnboarderSection::class);
    }

    public function convivientes(): HasMany
    {
        return $this->hasMany(UserConviviente::class);
    }

    public function convivienteTypes(): HasMany
    {
        return $this->hasMany(ConvivienteType::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(OnboarderMetric::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForWizard($query, int $wizardId)
    {
        return $query->where('wizard_id', $wizardId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isAbandoned(): bool
    {
        return $this->status === 'abandoned';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function markAsAbandoned(): void
    {
        $this->update([
            'status' => 'abandoned',
        ]);
    }

    public function getProgressPercentage(): int
    {
        $totalSections = $this->sections()->count();
        if ($totalSections === 0) {
            return 0;
        }

        $completedSections = $this->metrics()
            ->where('action', 'section_completed')
            ->count();

        return (int) round(($completedSections / $totalSections) * 100);
    }
}
