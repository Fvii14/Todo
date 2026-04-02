<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireQuestionSeeder2 extends Seeder
{
    public function run(): void
    {

        $preguntas = null;
        // Añadimos la 90 al BAJ de Navarra ID 21
        $preguntasNavarra = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 5,
            5 => 6,
            6 => 7,
            7 => 8,
            8 => 9,
            9 => 10,
            10 => 11,
            12 => 13,
            13 => 14,
            14 => 15,
            15 => 16,
            16 => 17,
            17 => 18,
            18 => 19,
            19 => 20,
            20 => 21,
            21 => 22,
            22 => 24,
            23 => 26,
            24 => 27,
            25 => 28,
            26 => 29,
            27 => 30,
            28 => 31,
            29 => 32,
        ];

        // Añadimos la 90 al BAJ de Baleares ID 12
        $preguntasBaleares = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 5,
            5 => 6,
            6 => 8,
            7 => 9,
            8 => 10,
            9 => 11,
            10 => 12,
            11 => 13,
            12 => 14,
            13 => 15,
            14 => 16,
            15 => 17,
            16 => 18,
            17 => 19,
            18 => 20,
            19 => 21,
            20 => 22,
            21 => 24,
            22 => 26,
            23 => 27,
            24 => 28,
            25 => 29,
            26 => 30,
            27 => 31,
            28 => 32,
        ];

        // Añadimos la 95 al BAJ de País Vasco ID 18
        $preguntasPaisVasco = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 5,
            5 => 6,
            6 => 8,
            7 => 9,
            8 => 10,
            9 => 11,
            11 => 13,
            12 => 14,
            13 => 15,
            14 => 16,
            15 => 17,
            16 => 18,
            17 => 19,
            18 => 20,
            19 => 21,
            20 => 23,
            21 => 25,
            22 => 26,
            23 => 27,
            24 => 28,
            25 => 29,
            26 => 30,
            27 => 31,
            28 => 32,
            29 => 96,
        ];

        // Preguntas específicas de BAJ Cataluña ID 3
        $preguntasCataluna = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 5,
            5 => 6,
            6 => 8,
            7 => 9,
            8 => 10,
            9 => 11,
            10 => 12,
            11 => 13,
            12 => 14,
            13 => 15,
            14 => 16,
            15 => 17,
            16 => 91,
            17 => 19,
            18 => 20,
            19 => 21,
            20 => 23,
            21 => 25,
            22 => 26,
            23 => 27,
            24 => 28,
            25 => 29,
            26 => 30,
            27 => 31,
            28 => 32,

        ];

        // Obtener todos los questionnaire_id entre 3 y 21 inclusive
        $questionnaires = DB::table('questionnaires')
            ->whereBetween('id', [3, 21])
            ->pluck('id');

        foreach ($questionnaires as $questionnaireId) {
            if ($questionnaireId == 3) {
                $preguntas = $preguntasCataluna;
            } elseif ($questionnaireId == 21) {
                $preguntas = $preguntasNavarra;
            } elseif ($questionnaireId == 12) {
                $preguntas = $preguntasBaleares;
            } elseif ($questionnaireId == 18) {
                $preguntas = $preguntasPaisVasco;
            } else {
                continue; // Si no es ninguno de los casos específicos, saltar al siguiente
            }

            foreach ($preguntas as $orden => $questionId) {
                DB::table('questionnaire_questions')->insert([
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $questionId,
                    'orden' => $orden,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
