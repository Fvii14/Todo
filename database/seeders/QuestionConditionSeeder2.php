<?php

namespace Database\Seeders;

use App\Models\Ayuda;
use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionConditionSeeder2 extends Seeder
{
    public function run(): void
    {
        $allQuestionnaireIds = range(1, 42);

        // Condiciones para preguntas relacionadas con formulario Collector
        $formCollector = [
            ['question_id' => 60, 'condition' => [1], 'next_question_id' => 34],
            ['question_id' => 99, 'condition' => [0], 'next_question_id' => 100],

        ];

        // 🟪 Preguntas GENERICAS
        $data = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 22],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 24],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 18, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 22, 'condition' => [0], 'next_question_id' => 24],
            ['question_id' => 23, 'condition' => [0], 'next_question_id' => 25],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],

        ];
        // 🟪 Preguntas para el formulario BAJ ID 3 (Catalunya)
        $dataCatalunya = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 91], // es la 18 para cataluña
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 23], // es la 22 de cataluña
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 25],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 91, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 23, 'condition' => [0], 'next_question_id' => 25],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],
        ];
        $dataAragonBAJ = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 25],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 91, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 23, 'condition' => [0], 'next_question_id' => 25],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],
        ];
        // 🟪 Preguntas para el formulario BAJ ID 21 (Navarra)
        $dataNavarra = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],

            // ['question_id' => 1, 'condition' => [3], 'next_question_id' => 90],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 22],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 24],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 91, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 22, 'condition' => [0], 'next_question_id' => 24],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],
        ];
        // 🟪 Preguntas para el formulario BAJ ID 12 (Baleares)
        $dataBaleares = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 22],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 24],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            // ['question_id' => 1, 'condition' => [3], 'next_question_id' => 90],

            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 18, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 22, 'condition' => [0], 'next_question_id' => 24],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],
        ];
        // 🟪 Preguntas para el formulario PAV ID 34 (Madrid)
        $dataMadridPAV = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 8],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 9],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 10],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 11],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            // ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 93],
            ['question_id' => 93, 'condition' => [0], 'next_question_id' => 94],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 22],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 24],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 18, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 22, 'condition' => [0], 'next_question_id' => 24],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],
        ];
        // Preguntas para el formulario PAV ID 32 (Andalucía)
        $dataAndaluciaPAV = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 8],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 9],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 10],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 11],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            // ['question_id' => 1, 'condition' => [2], 'next_question_id' => 3],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            // ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 22],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 24],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            ['question_id' => 96, 'condition' => [1], 'next_question_id' => 9], // 96 es pregunta grupo vulnerable 8
            ['question_id' => 96, 'condition' => [5], 'next_question_id' => 10],
            ['question_id' => 96, 'condition' => [6], 'next_question_id' => 97],
            ['question_id' => 96, 'condition' => [2], 'next_question_id' => 11],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 18, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 22, 'condition' => [0], 'next_question_id' => 24],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],
        ];

        $dataValenciaPAV = [
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 2],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 8],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 9],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 10],
            // ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 11],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 12],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 13],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 16],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 17],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 18],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 19],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 26],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 27],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 30],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 31],
            ['question_id' => 1, 'condition' => [0, 1, 3, 4], 'next_question_id' => 32],

            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 5],
            // ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 6],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 14],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 15],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 20],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 21],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 22],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 24],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 28],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 29],
            ['question_id' => 1, 'condition' => [3, 4], 'next_question_id' => 30],

            ['question_id' => 8, 'condition' => [1], 'next_question_id' => 9],
            ['question_id' => 8, 'condition' => [2], 'next_question_id' => 11],
            ['question_id' => 8, 'condition' => [5], 'next_question_id' => 10],

            ['question_id' => 12, 'condition' => [1], 'next_question_id' => 13],
            ['question_id' => 14, 'condition' => [1], 'next_question_id' => 15],
            ['question_id' => 16, 'condition' => [1], 'next_question_id' => 17],
            ['question_id' => 18, 'condition' => [0], 'next_question_id' => 19],
            ['question_id' => 20, 'condition' => [1], 'next_question_id' => 21],
            ['question_id' => 22, 'condition' => [0], 'next_question_id' => 24],
            ['question_id' => 23, 'condition' => [0], 'next_question_id' => 25],
            ['question_id' => 26, 'condition' => [0], 'next_question_id' => 27],
            ['question_id' => 29, 'condition' => [1], 'next_question_id' => 30],

        ];

        $imv = [
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 63],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 64],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 65],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 66],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 67],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 68],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 69],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 71],
            ['question_id' => 62, 'condition' => [2, 3, 4, 5, 6, 7, 8], 'next_question_id' => 74],
            ['question_id' => 63, 'condition' => [1, 2, 3, 4, 5], 'next_question_id' => 64],
            ['question_id' => 63, 'condition' => [2, 3, 4, 5], 'next_question_id' => 65],
            ['question_id' => 63, 'condition' => [3, 4, 5], 'next_question_id' => 66],
            ['question_id' => 63, 'condition' => [4, 5], 'next_question_id' => 67],
            ['question_id' => 63, 'condition' => [5], 'next_question_id' => 68],
            ['question_id' => 79, 'condition' => [0], 'next_question_id' => 80],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 63],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 64],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 65],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 66],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 67],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 68],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 69],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 71],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 74],
            ['question_id' => Question::where('slug', 'personas_convivencia_legal_ininterrumpida_1_anyo')->first()->id, 'condition' => [0], 'next_question_id' => Question::where('slug', 'motivo_unidad_convivencia_interrumpido_1_anyo')->first()->id],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 79],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 80],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 81],
            ['question_id' => 62, 'condition' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'next_question_id' => 82],

        ];

        foreach ($allQuestionnaireIds as $id) {
            switch ($id) {
                case 1:
                    $dataset = $formCollector;
                    break;
                case 3:
                    $dataset = $dataCatalunya;
                    break;
                case 12:
                    $dataset = $dataBaleares;
                    break;
                case 21:
                    $dataset = $dataNavarra;
                    break;
                case 23:
                    $dataset = $dataValenciaPAV;
                    break;
                case 32:
                    $dataset = $dataAndaluciaPAV;
                    break;
                case 34:
                    $dataset = $dataMadridPAV;
                    break;
                case 42:
                    $dataset = $imv;
                    break;
                case Ayuda::where('slug', 'baj_aragon')->first()->id:
                    $dataset = $dataAragonBAJ;
                    break;
                default:
                    if (in_array($id, [1, 2, 3, 12, 21, 32, 34, 42])) {
                        continue 2;
                    } // omitir duplicados explícitos
                    $dataset = $data;
            }

            $insertData = [];
            foreach ($dataset as $item) {
                $insertData[] = [
                    'questionnaire_id' => $id,
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

        $cienxhijoQuestionnaireId = Questionnaire::where('slug', 'ayuda_100_por_hijo')->first()->id;

        $cienxhijoConditions = [
            [
                'question_id' => Question::where('slug', 'otro_hijo_menor3')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([1, 2]),
                'next_question_id' => Question::where('slug', 'esta_trabajando')->first()->id,
                'operator' => '==',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'otro_hijo_menor3')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([1, 2]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'tienePrestaciones')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'otro_hijo_menor3')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([1, 2]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'cotizado_30_dias_nacimiento_hijo')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'otro_hijo_menor3')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([1, 2]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'eres_padre_madre')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'otro_hijo_menor3')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([1, 2]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'situaciones_100_por_hijo')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'esta_trabajando')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([0]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'tienePrestaciones')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'esta_trabajando')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([0]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'cotizado_30_dias_nacimiento_hijo')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'tienePrestaciones')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([0]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'cotizado_30_dias_nacimiento_hijo')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'eres_padre_madre')->first()->id,
                'questionnaire_id' => $cienxhijoQuestionnaireId,
                'condition' => json_encode([1]),
                'operator' => '==',
                'next_question_id' => Question::where('slug', 'situaciones_100_por_hijo')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('question_conditions')->insert($cienxhijoConditions);
    }
}
