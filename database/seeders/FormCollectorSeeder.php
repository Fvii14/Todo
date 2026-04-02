<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormCollectorSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $data = [];
        $formCollectorQuestionnaireId = Questionnaire::where('slug', 'form_collector')->first()->id;

        Question::insert([
            [
                'slug' => 'mensaje_collector_1',
                'text' => '🎉 ¡Genial! Ya tenemos una idea básica. Vamos a afinar un poco más tu perfil para poder decirte exactamente a qué puedes acceder.',
                'sub_text' => '',
                'type' => 'info',
                'created_at' => $now,
                'updated_at' => $now,
                'options' => json_encode([]),
                'regex_id' => null,
            ],
            [
                'slug' => 'mensaje_collector_2',
                'text' => '🔒 Tus datos están seguros. Sólo usaremos esta información para comprobar si cumples los requisitos de las ayudas. Nada más.',
                'sub_text' => '',
                'type' => 'info',
                'created_at' => $now,
                'updated_at' => $now,
                'options' => json_encode([]),
                'regex_id' => null,
            ],
            [
                'slug' => 'mensaje_collector_3',
                'text' => '💬 Ya casi lo tenemos. Con esta última información, podremos comprobar tu situación fiscal y decirte qué ayudas puedes recibir. No compartimos estos datos con terceros y se usan solo para esta comprobación.',
                'sub_text' => '',
                'type' => 'info',
                'created_at' => $now,
                'updated_at' => $now,
                'options' => json_encode([]),
                'regex_id' => null,
            ],
            [
                'slug' => 'seguro_deudas',
                'text' => '¡¡ATENTO!! Acabas de responder que SÍ tienes deudas con Hacienda o la Seguridad Social. ¿Es correcto?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
            ],
        ]);

        // 🟦 Preguntas para el formulario ID 1 Form collector
        $preguntasFormulario1 = [
            1 => Question::where('slug', 'provincia')->first()->id,
            2 => Question::where('slug', 'municipio')->first()->id,
            3 => Question::where('slug', 'esta_trabajando')->first()->id,
            4 => Question::where('slug', 'cual_fuente_de_ingresos')->first()->id,
            5 => Question::where('slug', 'tiene_hijos_o_pronto')->first()->id,
            6 => Question::where('slug', 'vives_alquiler')->first()->id,
            7 => Question::where('slug', 'quieres_vives_alquiler')->first()->id,
            8 => Question::where('slug', 'mensaje_collector_1')->first()->id,
            9 => Question::where('slug', 'fecha_nacimiento')->first()->id,
            10 => Question::where('slug', 'estado_civil')->first()->id,
            11 => Question::where('slug', 'genero')->first()->id,
            12 => Question::where('slug', 'propietario-vivienda')->first()->id,
            13 => Question::where('slug', 'situaciones-propietario')->first()->id,
            14 => Question::where('slug', 'tiene_dni')->first()->id,
            15 => Question::where('slug', 'telefono')->first()->id,
            16 => Question::where('slug', 'mensaje_collector_2')->first()->id,
            17 => Question::where('slug', 'dinero_ganado')->first()->id,
            18 => Question::where('slug', 'tiene_deudas')->first()->id,
            19 => Question::where('slug', 'seguro_deudas')->first()->id,
            20 => Question::where('slug', 'mensaje_collector_3')->first()->id,
        ];

        foreach ($preguntasFormulario1 as $orden => $questionId) {
            $data[] = [
                'questionnaire_id' => 1,
                'question_id' => $questionId,
                'orden' => $orden,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('questionnaire_questions')->insert($data);

        $cienxhijoConditions = [
            [
                'question_id' => Question::where('slug', 'esta_trabajando')->first()->id,
                'questionnaire_id' => $formCollectorQuestionnaireId,
                'condition' => json_encode([0]),
                'next_question_id' => Question::where('slug', 'cual_fuente_de_ingresos')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'vives_alquiler')->first()->id,
                'questionnaire_id' => $formCollectorQuestionnaireId,
                'condition' => json_encode([0]),
                'next_question_id' => Question::where('slug', 'quieres_vives_alquiler')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'propietario-vivienda')->first()->id,
                'questionnaire_id' => $formCollectorQuestionnaireId,
                'condition' => json_encode([1]),
                'next_question_id' => Question::where('slug', 'situaciones-propietario')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'question_id' => Question::where('slug', 'tiene_deudas')->first()->id,
                'questionnaire_id' => $formCollectorQuestionnaireId,
                'condition' => json_encode([1]),
                'next_question_id' => Question::where('slug', 'seguro_deudas')->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('question_conditions')->insert($cienxhijoConditions);
    }
}
