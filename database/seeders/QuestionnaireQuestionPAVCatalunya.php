<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireQuestionPAVCatalunya extends Seeder
{
    // ToDo: Meter la de empadronarse es uno de los requisitos bla bla bla
    public function run()
    {
        $questionnaireId = 22;
        $now = Carbon::now();

        // 1. Eliminar preguntas 22 y 24 del formulario 22
        DB::table('questionnaire_questions')
            ->where('questionnaire_id', $questionnaireId)
            ->whereIn('question_id', [22, 24])
            ->delete();

        // 2. Reordenar las preguntas actuales (para evitar conflictos de orden y huecos)
        $preguntasActuales = DB::table('questionnaire_questions')
            ->where('questionnaire_id', $questionnaireId)
            ->orderBy('orden')
            ->get();

        $orden = 1;
        foreach ($preguntasActuales as $pregunta) {
            DB::table('questionnaire_questions')
                ->where('id', $pregunta->id)
                ->update(['orden' => $orden++]);
        }

        // 3. Insertar preguntas 23 y 25 en el orden correcto (donde estaban la 22 y 24)
        //    Como ya se ha reordenado, las insertamos en los huecos que queremos

    }
}
