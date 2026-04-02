<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireQuestionSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $data = [];

        // 🟨 Preguntas para el formulario ID 42 IMV
        $preguntasFormulario42 = [

            1 => 62,
            2 => 63,
            3 => 64,
            4 => 65,
            5 => 66,
            6 => 67,
            7 => 68,
            8 => 69,
            9 => 70,
            10 => 71,
            11 => 73,
            12 => 74,
            13 => 75,
            14 => 76,
            15 => 77,
            16 => 78,
            17 => 79,
            18 => 80,
            19 => 81,
            20 => 82,
            21 => 83,
            22 => 84,
            23 => 85,
        ];

        foreach ($preguntasFormulario42 as $orden => $questionId) {
            $data[] = [
                'questionnaire_id' => 42,
                'question_id' => $questionId,
                'orden' => $orden,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 🟩 Preguntas válidas para formularios 22 al 41 (excluyen la 7, 23 y 25)
        // Son los formularios de Ayuda Estatal de Vivienda PAV y Bono alquiler joven con las mismas preguntas
        // ayudas estatales de vivienda PAV : 5,7,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40
        // Bono alquiler joven : 4,6,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41
        $BonoAlquilerJovenGeneral = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 5,
            5 => 6,
            6 => 8,
            7 => 9,
            8 => 10,
            9 => 11,
            10 => 14,
            11 => 15,
            12 => 16,
            13 => 17,
            14 => 18,
            15 => 19,
            16 => 20,
            17 => 21,
            18 => 22,
            19 => 24,
            20 => 26,
            21 => 27,
            22 => 28,
            23 => 29,
            24 => 30,
            25 => 31,
            26 => 32,
        ];
        $preguntasPlanEstatal = [
            1 => 1,
            2 => 2,
            3 => 5,
            4 => 8,
            5 => 9,
            6 => 10,
            7 => 11,
            8 => 14,
            9 => 15,
            10 => 16,
            11 => 17,
            12 => 18,
            13 => 19,
            14 => 20,
            15 => 21,
            16 => 22,
            17 => 24,
            18 => 26,
            19 => 27,
            20 => 28,
            21 => 29,
            22 => 30,
            23 => 31,
            24 => 32,
        ];

        // Formularios del PAV (Plan Estatal)
        foreach ([22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41] as $questionnaireId) {
            $orden = 1;
            foreach ($preguntasPlanEstatal as $questionId) {
                $data[] = [
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $questionId,
                    'orden' => $orden++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Formularios del Bono Alquiler Joven
        foreach ([4, 5, 6, 7, 8, 10, 11, 13, 14, 15, 16, 17, 19, 20] as $questionnaireId) {
            $orden = 1;
            foreach ($BonoAlquilerJovenGeneral as $questionId) {
                $data[] = [
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $questionId,
                    'orden' => $orden++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // 🟥 Preguntas para el formulario ID 2 Cien por hijo
        DB::table('questions')->insert([
            [
                'slug' => 'cotizado_30_dias_nacimiento_hijo',
                'text' => '¿Has cotizado al menos 30 días desde el nacimiento de tu hijo/a?',
                'sub_text' => '',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'eres_padre_madre',
                'text' => 'Eres...',
                'sub_text' => '',
                'type' => 'select',
                'options' => json_encode([
                    'Madre',
                    'Padre',
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'situaciones_100_por_hijo',
                'text' => '¿Te encuentras en alguna de estas situaciones?',
                'sub_text' => '',
                'type' => 'select',
                'options' => json_encode([
                    'Progenitores del mismo sexo',
                    'Padre o tutor en caso de fallecimiento de la madre',
                    'Padre o tutor con guarda y custodia en exclusiva',
                    'Ninguna de las anteriores',
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ],
        ]);
        $cienPorHijo = [
            1 => Question::where('slug', 'otro_hijo_menor3')->first()->id,
            2 => Question::where('slug', 'esta_trabajando')->first()->id,
            3 => Question::where('slug', 'tienePrestaciones')->first()->id,
            4 => Question::where('slug', 'cotizado_30_dias_nacimiento_hijo')->first()->id,
            5 => Question::where('slug', 'eres_padre_madre')->first()->id,
            6 => Question::where('slug', 'situaciones_100_por_hijo')->first()->id,
        ];

        foreach ($cienPorHijo as $orden => $questionId) {
            $data[] = [
                'questionnaire_id' => Questionnaire::where('slug', 'ayuda_100_por_hijo')->first()->id,
                'question_id' => $questionId,
                'orden' => $orden,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 🟥 Preguntas para el formulario PAV ID 32 (Andalucía)
        $preguntasAndalucia = [
            1,
            2,
            5,
            6,
            9,
            97,
            98,
            10,
            11,
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

        // 🟪 Preguntas para el formulario PAV ID 34 (Madrid)
        $preguntasMadrid = [
            1,
            2,
            5,
            93,
            94,
            8,
            9,
            10,
            11,
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

        // Carga estructurada para ambos
        $formulariosEspeciales = [
            32 => $preguntasAndalucia,
            34 => $preguntasMadrid,
        ];

        foreach ($formulariosEspeciales as $questionnaireId => $preguntas) {
            $orden = 1;
            foreach ($preguntas as $questionId) {
                $data[] = [
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $questionId,
                    'orden' => $orden++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        $data = collect($data)
            ->unique(fn ($item) => $item['questionnaire_id'].'-'.$item['question_id'])
            ->values()
            ->toArray();

        DB::table('questionnaire_questions')->insert($data);
    }
}
