<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regex extends Model
{
    // Establecemos el nombre de la tabla si no sigue la convención
    protected $table = 'regex';

    // Hacemos que los campos 'name' y 'pattern' sean asignables en masa
    protected $fillable = ['name', 'pattern'];

    // Relación con la tabla 'questions'
    public function questions()
    {
        // Una expresión regular puede estar asociada a muchas preguntas
        // y una pregunta puede tener una expresión regular
        return $this->hasMany(Question::class, 'regex_id');
    }
}
