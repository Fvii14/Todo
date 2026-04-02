<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionConditionConvivientesSeeder extends Seeder
{
    public function run(): void
    {
        // Cuestionarios de convivientes del 46 al 79
        $questionnaireIds = range(46, 79);

        // Generar todos los índices de nacionalidades excepto el 129 ("Española")
        $allExceptSpanish = array_values(array_filter(range(0, 263), fn ($i) => $i !== 129));

        // Condiciones a insertar
        $conditions = [
            ['question_id' => 148, 'condition' => [1], 'next_question_id' => 149],
            ['question_id' => 148, 'condition' => [1], 'next_question_id' => 150],
            ['question_id' => 148, 'condition' => [1], 'next_question_id' => 151],
            ['question_id' => 148, 'condition' => [1], 'next_question_id' => 152],
            ['question_id' => 151, 'condition' => [1], 'next_question_id' => 152],

            ['question_id' => 8, 'condition' => [0], 'next_question_id' => 156],
            ['question_id' => 156, 'condition' => [1], 'next_question_id' => 172],
            ['question_id' => 156, 'condition' => [1], 'next_question_id' => 173],

            ['question_id' => 156, 'condition' => [1], 'next_question_id' => 157],
            ['question_id' => 157, 'condition' => [1, 0], 'next_question_id' => 172],
            ['question_id' => 157, 'condition' => [1, 0], 'next_question_id' => 173],
            ['question_id' => 184, 'condition' => [1], 'next_question_id' => 179],
            ['question_id' => 179, 'condition' => [1], 'next_question_id' => 180],
            // Mostrar la 155 si la nacionalidad NO es Española
            ['question_id' => 127, 'condition' => $allExceptSpanish, 'next_question_id' => 155],
        ];

        // Ahora insertamos para cada cuestionario
        foreach ($questionnaireIds as $questionnaireId) {
            $insertData = [];

            foreach ($conditions as $item) {
                $insertData[] = [
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $item['question_id'],
                    'condition' => json_encode($item['condition']),
                    'operator' => '==',
                    'next_question_id' => $item['next_question_id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            DB::table('question_conditions')->insert($insertData);
        }
    }
}
