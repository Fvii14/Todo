<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arrendatario extends Model
{
    use HasFactory;

    protected $table = 'arrendatarios';

    protected $fillable = [
        'user_id',
        'index',
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con respuestas (answers)
    public function answers()
    {
        return $this->hasMany(Answer::class, 'arrendador_id');
    }
}
