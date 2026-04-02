<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboarderMetric extends Model
{
    protected $fillable = [
        'onboarder_id',
        'section_id',
        'conviviente_type_id',
        'action',
        'started_at',
        'completed_at',
        'duration_seconds',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function onboarder(): BelongsTo
    {
        return $this->belongsTo(Onboarder::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(OnboarderSection::class, 'section_id');
    }

    public function convivienteType(): BelongsTo
    {
        return $this->belongsTo(ConvivienteType::class);
    }

    public function scopeForOnboarder($query, int $onboarderId)
    {
        return $query->where('onboarder_id', $onboarderId);
    }

    public function scopeForSection($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    public function scopeForConvivienteType($query, int $convivienteTypeId)
    {
        return $query->where('conviviente_type_id', $convivienteTypeId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at')->whereNotNull('started_at');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->completed_at === null;
    }

    public function getDuration(): ?int
    {
        if ($this->isCompleted() && $this->started_at) {
            return $this->started_at->diffInSeconds($this->completed_at);
        }

        return $this->duration_seconds;
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'duration_seconds' => $this->getDuration(),
        ]);
    }

    public function getFormattedDuration(): string
    {
        $duration = $this->getDuration();
        if (! $duration) {
            return 'N/A';
        }

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $seconds);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        } else {
            return sprintf('%ds', $seconds);
        }
    }

    public static function createSectionStarted(int $onboarderId, int $sectionId): self
    {
        return self::create([
            'onboarder_id' => $onboarderId,
            'section_id' => $sectionId,
            'action' => 'section_started',
            'started_at' => now(),
        ]);
    }

    public static function createSectionCompleted(int $onboarderId, int $sectionId): self
    {
        return self::create([
            'onboarder_id' => $onboarderId,
            'section_id' => $sectionId,
            'action' => 'section_completed',
            'started_at' => now(),
            'completed_at' => now(),
            'duration_seconds' => 0,
        ]);
    }

    public static function createConvivienteStarted(int $onboarderId, int $convivienteTypeId): self
    {
        return self::create([
            'onboarder_id' => $onboarderId,
            'conviviente_type_id' => $convivienteTypeId,
            'action' => 'conviviente_started',
            'started_at' => now(),
        ]);
    }

    public static function createConvivienteCompleted(int $onboarderId, int $convivienteTypeId): self
    {
        return self::create([
            'onboarder_id' => $onboarderId,
            'conviviente_type_id' => $convivienteTypeId,
            'action' => 'conviviente_completed',
            'started_at' => now(),
            'completed_at' => now(),
            'duration_seconds' => 0,
        ]);
    }

    public static function createScreenStarted(int $onboarderId, int $sectionId, int $screenIndex, ?int $convivienteTypeId = null): self
    {
        return self::create([
            'onboarder_id' => $onboarderId,
            'section_id' => $sectionId,
            'conviviente_type_id' => $convivienteTypeId,
            'action' => 'screen_started',
            'started_at' => now(),
            'metadata' => ['screen_index' => $screenIndex],
        ]);
    }

    public static function createScreenCompleted(int $onboarderId, int $sectionId, int $screenIndex, ?int $convivienteTypeId = null): self
    {
        return self::create([
            'onboarder_id' => $onboarderId,
            'section_id' => $sectionId,
            'conviviente_type_id' => $convivienteTypeId,
            'action' => 'screen_completed',
            'started_at' => now(),
            'completed_at' => now(),
            'duration_seconds' => 0,
            'metadata' => ['screen_index' => $screenIndex],
        ]);
    }

    public function getScreenIndex(): ?int
    {
        return $this->metadata['screen_index'] ?? null;
    }
}
