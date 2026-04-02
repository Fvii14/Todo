<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireQuestionsSeeder4 extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $data = [];
        $BonoAlquilerJovenAragon = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 5,
            5 => 8,
            6 => 9,
            7 => 10,
            8 => 11,
            9 => 14,
            10 => 15,
            11 => 16,
            12 => 17,
            13 => 18,
            14 => 22,
            15 => 24,
            16 => 26,
            17 => 27,
            18 => 28,
            19 => 29,
            20 => 30,
            21 => 31,
            22 => 32,
        ];

        foreach ($BonoAlquilerJovenAragon as $orden => $questionId) {
            $data[] = [
                'questionnaire_id' => 9,
                'question_id' => $questionId,
                'orden' => $orden,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('questionnaire_questions')->insert($data);
    }
}
