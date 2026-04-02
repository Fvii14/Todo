<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionConditionSeeder extends Seeder
{
    public function run(): void
    { // CONDICIONES PARA PREGUNTAS RELACIONADAS CON AYUDA ALQUILER
        $questionnaireId = 41;
        $data = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 7], // NAVARRA
            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 22],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 23], // CATALUÑA
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 24],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 25],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],
            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 18, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 22, 'condition' => [0], 'next_question_id' => 14],
            ['question_id' => 23, 'condition' => [0], 'next_question_id' => 25],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],
        ];
        $questionnaireId = 43;
        $data = [
            ['question_id' => 99, 'condition' => [0], 'next_question_id' => 100],
        ];

        $insertData = [];

        foreach ($data as $item) {
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
