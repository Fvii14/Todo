<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddPreguntasCuestionarioPAVSeeder extends Seeder
{
    public function run(): void
    {
        // No añadiños preguntas al PAV de Navarra y País Vasco porque en esas comunidades no existe el PAV
        $questionnaireId = [22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 33, 35, 36, 38, 39, 41];

        $preguntas = null;

        $preguntasGenerales = [
            1,
            2,
            3,
            4,
            5,
            6,
            8,
            9,
            10,
            11,
            13,
            14,
            15,
            16,
            17,
            18,
            19,
            20,
            21,
            22,
            24,
            26,
            27,
            28,
            29,
            30,
            31,
            32,
        ];

        $preguntasMadrid = [
            1,
            2,
            3,
            4,
            5,
            8,
            9,
            10,
            11,
            13,
            14,
            15,
            16,
            17,
            18,
            19,
            20,
            21,
            22,
            24,
            26,
            27,
            28,
            29,
            30,
            31,
            32,
            92,
            93,
            94,
        ];

        $preguntasAndalucia = [
            1,
            2,
            3,
            4,
            5,
            6,
            9,
            10,
            11,
            13,
            14,
            15,
            16,
            17,
            18,
            19,
            20,
            21,
            22,
            24,
            26,
            27,
            28,
            29,
            30,
            31,
            32,
            96,
            97,
        ];

        // Obtener todos los questionnaire_id entre 22 y 41 inclusive (los PAV)
        $questionnaires = DB::table('questionnaires')
            ->whereBetween('id', [22, 41])
            ->pluck('id');

        foreach ($questionnaires as $questionnaireId) {
            if ($questionnaireId == 32) {
                $preguntas = $preguntasAndalucia;
            } elseif ($questionnaireId == 34) {
                $preguntas = $preguntasMadrid;
            } else {
                $preguntas = $preguntasGenerales;
            }

            foreach ($preguntas as $questionId) {
                DB::table('questionnaire_questions')->insert([
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $questionId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
