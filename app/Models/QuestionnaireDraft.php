<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireDraft extends Model
{
    use HasFactory;

    protected $table = 'questionnaire_drafts';

    protected $fillable = [
        'user_id',
        'questionnaire_id',
        'direction',
        'time_start',
        'time_end',
        'respuesta',
        'session_id',
        'session_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }
}
