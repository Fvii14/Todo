<?php

namespace Database\Seeders;

use App\Models\Ayuda;
use App\Models\Document;
use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudasHijosMadridGaliciaSeeder extends Seeder
{
    public function run()
    {
        // SEEDER PARA AYUDA MADRID. COMPLETA
        DB::table('ayudas')->insert([
            [
                'nombre_ayuda' => 'Ayuda 500€ al mes por hijo en Madrid',
                'sector' => 'familia',
                'ccaa_id' => 3,
                'create_time' => null,
                'presupuesto' => 114000000,
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'description' => 'La Comunidad de Madrid ofrece una ayuda de 500€ al mes por hijo para madres menores de 30 años con ingresos bajos. Está disponible desde la semana 21 de embarazo hasta los 2 años del niño y es compatible con otras ayudas.',
                'organo_id' => 3,
                'activo' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cuantia_usuario' => 6000,
                'slug' => 'ayuda_500_hijo_madrid',
            ],
        ]);
        $ayudaId = DB::table('ayudas')->where('slug', 'ayuda_500_hijo_madrid')->value('id');
        DB::table('questionnaires')->insert([
            [
                'name' => 'Ayuda 500€ al mes por hijo en Madrid',
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'slug' => 'ayuda_500_hijo_madrid',
                'ayuda_id' => $ayudaId,
                'tipo' => 'pre',
            ],
        ]);
        $questionnaireId = DB::table('questionnaires')->where('slug', 'ayuda_500_hijo_madrid')->value('id');
        DB::table('ayudas')->where('id', $ayudaId)->update([
            'questionnaire_id' => $questionnaireId,
        ]);
        // DB::table('questions')->insert([
        //     'slug' => "tiene_menos_31_years",
        //     'text' => "¿Tienes menos de 31 años?",
        //     'sub_text' => null,
        //     'type' => "boolean",
        //     'options' => json_encode([]),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        //     'regex_id' => null,
        //     'exclude_none_option' => false,
        // ]);
        DB::table('questions')->insert([
            'slug' => 'residir_empadronado_madrid_5_de_10_years_y_mantener_residencia',
            'text' => '¿Estás residiendo y empadronada en la comunidad de madrid durante al menos 5 de los últimos 10 años anteriores a la fecha de presentación de la solicitud?',
            'sub_text' => 'Ten en cuenta además, que debes mantener tu residencia en la comunidad de madrid durante al menos el tiempo que percibas la ayuda.',
            'type' => 'boolean',
            'options' => json_encode([]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);
        DB::table('questions')->insert([
            'slug' => 'no_superar_30k_individual_o_36.2_conjunta_ultimo_irpf',
            'text' => '¿Has cobrado menos de 30.000 euros de renta individualmente o 36.200 euros de renta conjunta?',
            'sub_text' => null,
            'type' => 'boolean',
            'options' => json_encode([]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);
        DB::table('questions')->insert([
            'slug' => 'es_gestante_semana21_madre_hijos_menores_24_meses_madre_adoptado',
            'text' => '¿Tu situación coincide con alguna de las siguientes?',
            'sub_text' => 'Aplica igual si eres la persona adoptante o quien posee la patria potestad',
            'type' => 'select',
            'options' => json_encode(['Ninguna de las anteriores', 'Soy gestante y estoy en la semana 21 de gestación', 'Soy madre y he tenido uno o más hijos con menos de 24 meses', 'Soy madre y he adoptado y registrado en un período inferior a 24 meses']),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);
        DB::table('questionnaire_questions')->insert([
            'questionnaire_id' => Questionnaire::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
            'question_id' => Question::where('slug', 'residir_empadronado_madrid_5_de_10_years_y_mantener_residencia')->value('id'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('questionnaire_questions')->insert([
            'questionnaire_id' => Questionnaire::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
            'question_id' => Question::where('slug', 'no_superar_30k_individual_o_36.2_conjunta_ultimo_irpf')->value('id'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('questionnaire_questions')->insert([
            'questionnaire_id' => Questionnaire::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
            'question_id' => Question::where('slug', 'es_gestante_semana21_madre_hijos_menores_24_meses_madre_adoptado')->value('id'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $requisitos = [
            // [
            //     'descripcion' => 'Tener menos de 31 años',
            //     'json_regla' => json_encode([
            //         'condition' => 'AND',
            //         'rules' => [
            //             ['question_id' => Question::where('slug', 'tiene_menos_31_years')->value('id'), 'operator' => '<=', 'value' => 31]
            //         ]
            //     ])
            // ],
            [
                'descripcion' => 'Debes tener DNI o NIE ',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'tiene_dni')->value('id'), 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Debes haber estado empadronada en la comunidad de madrid durante al menos 5 de los últimos 10 años anteriores a la fecha de presentación de la solicitud',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'residir_empadronado_madrid_5_de_10_years_y_mantener_residencia')->value('id'), 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No podías superar los 30.000€ anuales de renta individual o 36.200€ euros anuales de renta conjunta',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'no_superar_30k_individual_o_36.2_conjunta_ultimo_irpf')->value('id'), 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No puedes tener deudas con Hacienda o la seguridad social',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'tiene_deudas')->value('id'), 'operator' => '==', 'value' => 0],
                    ],
                ]),
            ],
        ];
        // DB::table('ayuda_requisitos_json')->insert([
        //     'ayuda_id' => Ayuda::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
        //     'descripcion' => 'Requisitos para la ayuda de 500€ de la comunidad de madrid',
        //     'json_regla' => json_encode($requisitos),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
        DB::table('ayuda_documentos')->insert([
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
                'documento_id' => Document::where('slug', 'padron-historico')->value('id'),
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
                'documento_id' => Document::where('slug', 'informe_medico_gestacion')->value('id'),
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_500_hijo_madrid')->value('id'),
                'documento_id' => Document::where('slug', 'certificado_nacimiento')->value('id'),
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // SEEDER PARA AYUDA GALICIA. COMPLETA
        DB::table('ayudas')->insert([
            [
                'nombre_ayuda' => 'Ayuda Tarxeta Benvida',
                'sector' => 'familia',
                'ccaa_id' => 6,
                'create_time' => null,
                'presupuesto' => 31000000,
                'fecha_inicio' => '2025-01-30',
                'fecha_fin' => '2025-12-31',
                'description' => 'La Xunta de Galicia concede una ayuda directa en formato de tarjeta prepago para familias con hijos/as nacidos, adoptados o acogidos en 2025. La cuantía parte de 100€/mes por hijo/a el primer año y puede renovarse los dos siguientes si se mantienen los ingresos bajos. Es compatible con otras ayudas y se concede por orden de solicitud. La tarjeta solo puede usarse para productos infantiles, y el presupuesto total asciende a 31,9 millones de euros.',
                'organo_id' => 6,
                'activo' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cuantia_usuario' => 2400,
                'slug' => 'ayuda_galicia_tarxeta_benvida',
            ],
        ]);
        $ayudaId = DB::table('ayudas')->where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id');
        DB::table('questionnaires')->insert([
            [
                'name' => 'Ayuda Tarxeta Benvida',
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'slug' => 'ayuda_galicia_tarxeta_benvida',
                'ayuda_id' => $ayudaId,
                'tipo' => 'pre',
            ],
        ]);
        $questionnaireId = DB::table('questionnaires')->where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id');
        DB::table('ayudas')->where('id', $ayudaId)->update([
            'questionnaire_id' => $questionnaireId,
        ]);
        // DB::table('questions')->insert([
        //     'slug' => "casos_galicia_tarxeta_benvida",
        //     'text' => "¿Te encuentras en alguno de los siguientes casos?",
        //     'sub_text' => null,
        //     'type' => "select",
        //     'options' => json_encode(["He tenido hijos/as nacidos entre el 1 de enero y el 31 de Diciembre de 2025 y no han pasado 2 meses desde su nacimiento o adopción", "Estoy en la semana 21 o posterior de la gestación", "Ninguna de los anteriores"]),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        //     'regex_id' => null,
        //     'exclude_none_option' => false,
        // ]);
        DB::table('questions')->insert([
            'slug' => 'residir_empadronado_galicia',
            'text' => '¿Resides y estás empadronado en Galicia?',
            'sub_text' => null,
            'type' => 'boolean',
            'options' => json_encode([]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);
        DB::table('questions')->insert([
            'slug' => 'renta_inferior_45k',
            'text' => '¿La renta de la unidad familiar supera los 45.000€?',
            'sub_text' => null,
            'type' => 'boolean',
            'options' => json_encode([]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);
        // La pregunta de a continuación no es para este seeder pero se mete para que se quede al final y no crear un seeder nuevo solo para esta pregunta
        DB::table('questions')->insert([
            'slug' => 'fecha_formulario_inicial',
            'text' => 'PREGUNTA INTERNA. Fecha en la que hizo el formulario inicial, indistintamente de si bankflip o no',
            'sub_text' => 'PREGUNTA INTERNA. Fecha en la que hizo el formulario inicial, indistintamente de si bankflip o no',
            'type' => 'date',
            'options' => json_encode([]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'regex_id' => null,
            'exclude_none_option' => false,
        ]);
        // DB::table('questionnaire_questions')->insert([
        //     'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
        //     'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
        DB::table('questionnaire_questions')->insert([
            'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
            'question_id' => Question::where('slug', 'residir_empadronado_galicia')->value('id'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('questionnaire_questions')->insert([
            'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
            'question_id' => Question::where('slug', 'renta_inferior_45k')->value('id'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $requisitos = [
            [
                'descripcion' => 'Debes tener DNI o NIE ',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'tiene_dni')->value('id'), 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Tu hijo/a debe haber nacido entre el 1 de enero y el 31 de diciembre de 2025. Además, debe haber pasado menos de 2 meses desde su nacimiento o adopción. O bien, si eres gestante, debes estar en la semana 21 o posterior',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'hijos_en_2025_menos_2_meses_nacimiento_adopcion')->value('id'), 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Debes residir y además estar empadronado en la comunidad de galicia',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'residir_empadronado_galicia')->value('id'), 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'La renta de tu unidad familiar no puede superar los 45.000€',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'renta_inferior_45k')->value('id'), 'operator' => '==', 'value' => 0],
                    ],
                ]),
            ],
        ];
        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => Ayuda::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
            'descripcion' => 'Requisitos para la ayuda Tarxeta Benvida de Galicia',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('ayuda_documentos')->insert([
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
                'documento_id' => Document::where('slug', 'informe_medico_gestacion')->value('id'),
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
                'documento_id' => Document::where('slug', 'libro-familia-certificado-registro-civil')->value('id'),
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // DB::table('question_conditions')->insert([
        //     [
        //         'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
        //         'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
        //         'condition' => json_encode([0,1]),
        //         'next_question_id' => Question::where('slug', 'residir_empadronado_galicia')->value('id'),
        //         'created_at' => Carbon::now(),
        //     ],
        //     [
        //         'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
        //         'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
        //         'condition' => json_encode([0,1]),
        //         'next_question_id' => Question::where('slug', 'residir_empadronado_galicia')->value('id'),
        //         'created_at' => Carbon::now(),
        //     ],
        //     [
        //         'question_id' => Question::where('slug', 'casos_galicia_tarxeta_benvida')->value('id'),
        //         'questionnaire_id' => Questionnaire::where('slug', 'ayuda_galicia_tarxeta_benvida')->value('id'),
        //         'condition' => json_encode([0,1]),
        //         'next_question_id' => Question::where('slug', 'renta_inferior_45k')->value('id'),
        //         'created_at' => Carbon::now(),
        //     ]
        // ]);

        // Ayuda Bono Cultural Joven 2025
        DB::table('ayudas')->insert([
            [
                'nombre_ayuda' => 'Bono Cultural Joven 2025',
                'sector' => 'joven',
                'ccaa_id' => null,
                'create_time' => null,
                'presupuesto' => null,
                'fecha_inicio_periodo' => '2025-06-16',
                'fecha_fin_periodo' => '2025-10-31',
                'organo_id' => 20,
                'activo' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cuantia_usuario' => 400,
                'pago' => 1,
                'slug' => 'bono_cultural_joven_2025',
            ],
        ]);
        $requisitos = [
            [
                'descripcion' => 'Debes tener DNI o NIE ',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'tiene_dni')->value('id'), 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Esta ayuda es para nacid@s en 2007',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'fecha_nacimiento')->value('id'), 'operator' => 'born_in_year', 'value' => '2007'],
                    ],
                ]),
            ],
        ];
        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => Ayuda::where('slug', 'bono_cultural_joven_2025')->value('id'),
            'descripcion' => 'Requisitos para el Bono Cultural Joven 2025',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('products')->insert([
            [
                'ayudas_id' => Ayuda::where('slug', 'bono_cultural_joven_2025')->value('id'),
                'product_name' => 'Bono Cultural Joven 2025',
                'stripe_product_id' => 'prod_Sba8QUqGa99Xi2',
                'price_id' => 'price_1ReCOMCmfwYMzkRXUgxJBvJc',
                'price' => 19.99,
                'currency' => 'eur',
                'payment_type' => 'one_time',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
        DB::table('documents')->insert([
            [
                'name' => 'DNI usuario',
                'slug' => 'dni_usuario',
                'por_conviviente' => false,
                'description' => ' Tu DNI',
                'allowed_types' => 'application/pdf, image/jpeg, image/png',
                'tipo' => 'general',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
        DB::table('ayuda_documentos')->insert([
            [
                'ayuda_id' => Ayuda::where('slug', 'bono_cultural_joven_2025')->value('id'),
                'documento_id' => Document::where('slug', 'dni_usuario')->value('id'),
                'es_obligatorio' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'bono_cultural_joven_2025')->value('id'),
                'documento_id' => Document::where('slug', 'firma')->value('id'),
                'es_obligatorio' => 1,
            ],
        ]);
    }
}
