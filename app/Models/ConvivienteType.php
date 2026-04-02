<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConvivienteType extends Model
{
    protected $fillable = [
        'onboarder_id',
        'name',
        'description',
        'icon',
        'order',
    ];

    public function onboarder(): BelongsTo
    {
        return $this->belongsTo(Onboarder::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ConvivienteTypeSection::class)->orderBy('order');
    }

    public function userConvivientes(): HasMany
    {
        return $this->hasMany(UserConviviente::class);
    }

    public function onboarderSections(): BelongsToMany
    {
        return $this->belongsToMany(OnboarderSection::class, 'conviviente_type_sections', 'conviviente_type_id', 'onboarder_section_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(OnboarderMetric::class, 'conviviente_type_id');
    }

    public function scopeForWizard($query, int $wizardId)
    {
        return $query->where('wizard_id', $wizardId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getQuestionsCount(): int
    {
        return $this->sections()
            ->withCount('questions')
            ->get()
            ->sum('questions_count');
    }

    public function getSectionsCount(): int
    {
        return $this->sections()->count();
    }

    public function getDisplayName(): string
    {
        return $this->name;
    }

    public function getIconClass(): string
    {
        return $this->icon ?? 'fas fa-user';
    }
}
