<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionConditionGananciaSeeder extends Seeder
{
    public function run(): void
    {
        // IDs de las preguntas por slug
        $questionId = DB::table('questions')->where('slug', 'ganancia-total')->value('id');
        $nextQuestionIds = DB::table('questions')
            ->whereIn('slug', ['ganancia-mayor-25200', 'aviso-ganancia-mayor-25200'])
            ->pluck('id', 'slug')
            ->toArray();

        // Cuestionarios del 2 al 42
        $questionnaireIds = range(2, 42);
        $value = 25200;

        foreach ($questionnaireIds as $questionnaireId) {
            foreach ($nextQuestionIds as $slug => $nextId) {
                DB::table('question_conditions')->insert([
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $questionId,
                    'condition' => json_encode([$value]),
                    'operator' => '>',
                    'next_question_id' => $nextId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

    }
}
