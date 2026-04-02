<?php

namespace Database\Seeders;

use App\Models\Ayuda;
use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudasHijosMadridGaliciaSeeder2 extends Seeder
{
    public function run()
    {

        DB::table('questions')->insert([
            'slug' => 'tiene_menos_31_years',
            'text' => '¿Tienes menos de 31 años?',
            'sub_text' => null,
            'type' => 'boolean',
            'options' => json_encode([]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);

        DB::table('questions')->insert([
            'slug' => 'casos_galicia_tarxeta_benvida',
            'text' => '¿Te encuentras en alguno de los siguientes casos?',
            'sub_text' => null,
            'type' => 'select',
            'options' => json_encode(['He tenido hijos/as nacidos entre el 1 de enero y el 31 de Diciembre de 2025 y no han pasado 2 meses desde su nacimiento o adopción', 'Estoy en la semana 21 o posterior de la gestación', 'Ninguna de los anteriores']),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);

        $requisitos = [
            [
                'descripcion' => 'Tener menos de 31 años',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'tiene_menos_31_years')->value('id'), 'operator' => '<=', 'value' => 31],
                    ],
                ]),
            ],
        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => Ayuda::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
            'descripcion' => 'Requisitos para la ayuda de 500€ de la comunidad de madrid',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('question_conditions')->insert([
            [
                'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
                'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
                'condition' => json_encode([0, 1]),
                'next_question_id' => Question::where('slug', 'residir_empadronado_galicia')->value('id'),
                'created_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
                'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
                'condition' => json_encode([0, 1]),
                'next_question_id' => Question::where('slug', 'residir_empadronado_galicia')->value('id'),
                'created_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
                'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
                'condition' => json_encode([0, 1]),
                'next_question_id' => Question::where('slug', 'renta_inferior_45k')->value('id'),
                'created_at' => Carbon::now(),
            ],
        ]);

        DB::table('questionnaire_questions')->insert([
            'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
            'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
