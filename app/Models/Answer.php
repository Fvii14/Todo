<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Answer extends Model
{
    protected $table = 'answers';

    protected $fillable = [
        'answer',
        'user_id',
        'question_id',
        'conviviente_id',
        'arrendador_id',
        'onboarder_id',
        'user_conviviente_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function getFormattedAnswer()
    {
        if ($this->question->slug == 'comunidad_autonoma') {
            if (is_string($this->answer) && ! is_numeric($this->answer)) {
                return $this->answer;
            }
            $ccaa = Ccaa::find($this->answer);

            return $ccaa ? $ccaa->nombre_ccaa : $this->answer;
        } elseif ($this->question->slug == 'provincia') {
            $provincia = Provincia::find($this->answer);

            return $provincia ? $provincia->nombre_provincia : $this->answer;
        } elseif ($this->question->slug == 'municipio') {
            $municipio = Municipio::find($this->answer);

            return $municipio ? $municipio->nombre_municipio : $this->answer;
        } elseif ($this->question->slug == 'estado_civil') {
            $estadosCiviles = [
                1 => 'Soltero/a',
                2 => 'Casado/a',
                3 => 'Viudo/a',
                4 => 'Divorciado/a',
            ];

            return $estadosCiviles[$this->answer] ?? $this->answer;
        } elseif ($this->question->slug == 'sexo') {
            $sexos = [
                'H' => 'Hombre',
                'M' => 'Mujer',
            ];

            return $sexos[$this->answer] ?? $this->answer;
        } elseif ($this->question->type == 'boolean') {
            return ($this->answer == '1' || $this->answer == 'true') ? 'Sí' : 'No';
        } elseif ($this->question->type == 'select' && $this->question->options) {
            $options = is_string($this->question->options) ? json_decode($this->question->options, true) : $this->question->options;

            return $options[$this->answer] ?? $this->answer;
        } elseif ($this->question->type == 'multiple' && $this->question->options) {
            $options = is_string($this->question->options) ? json_decode($this->question->options, true) : $this->question->options;
            $selectedValues = explode(',', $this->answer);
            $formattedValues = [];
            foreach ($selectedValues as $value) {
                $formattedValues[] = $options[trim($value)] ?? trim($value);
            }

            return implode(', ', $formattedValues);
        }

        return $this->answer;
    }

    public function conviviente()
    {
        return $this->belongsTo(Conviviente::class);
    }

    public function arrendador()
    {
        return $this->belongsTo(Arrendatario::class, 'arrendador_id');
    }

    public function onboarder()
    {
        return $this->belongsTo(Onboarder::class, 'onboarder_id');
    }

    public function userConviviente()
    {
        return $this->belongsTo(UserConviviente::class, 'user_conviviente_id');
    }

    /**
     * Devolvemos las answers de un usuario en formato
     * coleccion ('answer', 'question_id')
     *
     * @param  User  $userId
     * @return Collection
     */
    public static function getColectionAnswersQuestions(int $userId)
    {

        $answers = DB::table('answers')
            ->where('user_id', $userId)
            ->pluck('answer', 'question_id')
            // Si la respuesta es tipo json($a=true) decodificamos sino devolvemos valor tal cual
            ->map(function ($a) {
                if (\is_string($a)) {
                    $decoded = \json_decode($a, true);
                    $jsonOk = \json_last_error() === JSON_ERROR_NONE;
                    // Si el json esta mal log de error
                    if (! $jsonOk) {
                        Log::error('Error al recoger coleccion Answer/Question_id : '.\json_last_error_msg());
                    }

                    return $jsonOk ? $decoded : $a;
                }

                return $a;
            });

        return $answers;
    }

    /* ---------- Scopes para consultas reutilizables ---------- */

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por pregunta
     */
    public function scopeByQuestion($query, int $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Scope para filtrar por múltiples preguntas
     */
    public function scopeByQuestions($query, array $questionIds)
    {
        return $query->whereIn('question_id', $questionIds);
    }

    /**
     * Scope para excluir respuestas de convivientes
     */
    public function scopeWithoutConviviente($query)
    {
        return $query->whereNull('conviviente_id');
    }

    /**
     * Scope para filtrar por conviviente
     */
    public function scopeByConviviente($query, int $convivienteId)
    {
        return $query->where('conviviente_id', $convivienteId);
    }

    /**
     * Scope para obtener respuestas de un cuestionario específico
     */
    public function scopeForQuestionnaire($query, int $questionnaireId)
    {
        return $query->whereIn('question_id', function ($q) use ($questionnaireId) {
            $q->select('question_id')
                ->from('questionnaire_questions')
                ->where('questionnaire_id', $questionnaireId);
        });
    }
}
