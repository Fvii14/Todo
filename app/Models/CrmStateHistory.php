<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmStateHistory extends Model
{
    use HasFactory;

    protected $table = 'crm_state_history';

    protected $fillable = [
        'user_id',
        'ayuda_id',
        'from_stage',
        'to_stage',
        'from_temp',
        'to_temp',
        'event',
        'meta',
        'change_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'change_by');
    }

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }
}
