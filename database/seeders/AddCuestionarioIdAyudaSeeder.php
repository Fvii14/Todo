<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddCuestionarioIdAyudaSeeder extends Seeder
{
    public function run(): void
    {
        // Array de ayuda_id => questionnaire_id
        $updates = [
            4 => 3,
            6 => 4,
            9 => 5,
            11 => 6,
            13 => 7,
            15 => 8,
            17 => 9,
            19 => 10,
            21 => 11,
            23 => 12,
            25 => 13,
            27 => 14,
            29 => 15,
            31 => 16,
            33 => 17,
            35 => 18,
            37 => 19,
            39 => 20,
            41 => 21,
            5 => 22,
            7 => 23,
            8 => 24,
            10 => 25,
            12 => 26,
            14 => 27,
            16 => 28,
            18 => 29,
            20 => 30,
            22 => 31,
            24 => 32,
            26 => 33,
            28 => 34,
            30 => 35,
            32 => 36,
            34 => 37,
            36 => 38,
            38 => 39,
            40 => 40,
            1 => 2,
            2 => 42,
        ];

        foreach ($updates as $ayudaId => $questionnaireId) {
            DB::table('questionnaires')
                ->where('id', $questionnaireId)
                ->update([
                    'ayuda_id' => $ayudaId,
                ]);
        }
    }
}
