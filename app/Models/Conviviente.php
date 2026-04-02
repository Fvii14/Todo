<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conviviente extends Model
{
    use HasFactory;

    protected $table = 'convivientes';

    protected $fillable = [
        'user_id',
        'index',
        'token',
        'tipo',
    ];

    // No hay casts porque no hay esas columnas
    protected $casts = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'conviviente_id');
    }

    public static function countByUser($userId)
    {
        return self::where('user_id', $userId)->count();
    }

    public function nombre()
    {
        $respuestas = Answer::where('conviviente_id', $this->id)
            ->whereIn('question_id', [177, 170, 171])
            ->pluck('answer', 'question_id');
        $nombre = $respuestas[177] ?? null;
        $apellido1 = $respuestas[170] ?? null;
        $apellido2 = $respuestas[171] ?? null;

        return collect([$nombre, $apellido1, $apellido2])
            ->filter()
            ->implode(' ');
    }

    public function esMayorQue(int $years = 18): bool
    {
        // Obtener respuesta de la fecha de nacimiento
        $fecha = Answer::where('conviviente_id', $this->id)
            ->where('question_id', 40)
            ->value('answer'); // formato YYYY-MM-DD

        // Si no tiene respuesta, NO es mayor
        if (! $fecha) {
            return true;
        }
        $date = Carbon::parse($fecha);

        // Comparar si es mayor o igual a X años
        return $date->lte(Carbon::now()->subYears($years));
    }

    // Esta funcion filtra los convivientes por el ID del usuario
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Crear registro conviviente
    public function convivientes()
    {
        return $this->hasMany(Conviviente::class);
    }
}
