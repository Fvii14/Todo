<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionsCoditionsSolicitud extends Seeder
{
    public function run(): void
    {
        // Definir las condiciones para los formularios
        $otra_ayuda = [
            ['question_id' => 147, 'condition' => [1], 'next_question_id' => 148],
            ['question_id' => 147, 'condition' => [1], 'next_question_id' => 149],
            ['question_id' => 147, 'condition' => [1], 'next_question_id' => 150],
            ['question_id' => 147, 'condition' => [1], 'next_question_id' => 151],
            ['question_id' => 147, 'condition' => [1], 'next_question_id' => 152],
            ['question_id' => 151, 'condition' => [1], 'next_question_id' => 152],
            ['question_id' => 182, 'condition' => [1], 'next_question_id' => 183],
            ['question_id' => 182, 'condition' => [1], 'next_question_id' => 172],
            ['question_id' => 182, 'condition' => [1], 'next_question_id' => 173],
            ['question_id' => 183, 'condition' => [1, 0], 'next_question_id' => 172],
            ['question_id' => 183, 'condition' => [1, 0], 'next_question_id' => 173],
            ['question_id' => 8, 'condition' => [1, 2, 3, 4, 5], 'next_question_id' => 182],
            ['question_id' => 8, 'condition' => [1, 2, 3, 4, 5], 'next_question_id' => 183],
            ['question_id' => 8, 'condition' => [1, 2, 3, 4, 5], 'next_question_id' => 172],
            ['question_id' => 8, 'condition' => [1, 2, 3, 4, 5], 'next_question_id' => 173],

        ];

        // Iterar para formularios con IDs desde 80 hasta 122
        for ($id = 80; $id <= 122; $id++) {
            $insertData = [];
            foreach ($otra_ayuda as $item) {
                $insertData[] = [
                    'questionnaire_id' => $id, // Asignamos el ID del formulario
                    'question_id' => $item['question_id'],
                    'condition' => json_encode($item['condition']),
                    'operator' => '==',
                    'next_question_id' => $item['next_question_id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

            }

            // Insertamos las condiciones en la tabla 'question_conditions'
            DB::table('question_conditions')->insert($insertData);
        }
    }
}
