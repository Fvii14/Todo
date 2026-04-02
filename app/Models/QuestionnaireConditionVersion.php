<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class QuestionnaireConditionVersion extends Model
{
    protected $table = 'questionnaire_conditions_versions';

    protected $fillable = [
        'questionnaire_id',
        'version_number',
        'conditions_data',
        'is_active',
        'is_draft',
        'created_by',
        'published_at',
        'version_description',
    ];

    protected $casts = [
        'conditions_data' => 'array',
        'is_active' => 'boolean',
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getActiveVersion($questionnaireId)
    {
        return self::where('questionnaire_id', $questionnaireId)
            ->where('is_active', true)
            ->first();
    }

    public static function getCurrentDraft($questionnaireId)
    {
        return self::where('questionnaire_id', $questionnaireId)
            ->where('is_draft', true)
            ->orderBy('version_number', 'desc')
            ->first();
    }

    public static function getNextVersionNumber($questionnaireId)
    {
        $maxVersion = self::where('questionnaire_id', $questionnaireId)->max('version_number');

        return ($maxVersion ?? 0) + 1;
    }

    public function publish()
    {
        self::where('questionnaire_id', $this->questionnaire_id)
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
            'questionnaire_id' => $this->questionnaire_id,
            'version_number' => self::getNextVersionNumber($this->questionnaire_id),
            'conditions_data' => $this->conditions_data,
            'is_active' => false,
            'is_draft' => true,
            'created_by' => Auth::user()->id ?? 1,
            'version_description' => 'Draft basado en versión '.$this->version_number,
        ]);
    }

    public static function createFromCurrentConditions($questionnaireId, $description = null)
    {
        $conditions = QuestionCondition::where('questionnaire_id', $questionnaireId)->orderBy('order')->get()->toArray();

        return self::create([
            'questionnaire_id' => $questionnaireId,
            'version_number' => self::getNextVersionNumber($questionnaireId),
            'conditions_data' => $conditions,
            'is_active' => false,
            'is_draft' => true,
            'created_by' => Auth::user()->id ?? 1,
            'version_description' => $description ?? 'Nuevo draft',
        ]);
    }
}
