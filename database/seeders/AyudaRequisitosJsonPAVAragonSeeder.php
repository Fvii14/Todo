<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonPAVAragonSeeder extends Seeder
{
    public function run(): void
    {
        $requisitos = [
            [
                'descripcion' => 'Ser mayor de edad',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => 40,
                            'operator' => 'greater_than_years',
                            'value' => 18,
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Tener contrato de alquiler firmado',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => 1,
                            'operator' => '!=',
                            'value' => 'Todavía no tengo contrato de alquiler firmado.',
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Estar empadronado en la vivienda de alquiler',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'question_id' => 26,
                            'operator' => '==',
                            'value' => 1,
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 26, 'operator' => '==', 'value' => 0],
                                ['question_id' => 27, 'operator' => '==', 'value' => 1],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No tener propiedades (salvo excepciones justificadas)',
                'json_regla' => json_encode([
                    'condition' => 'AND',   // ambas sub-normas deben cumplirse
                    'rules' => [

                        // 1) Sub-norma solicitante
                        [
                            'condition' => 'OR',
                            'rules' => [
                                // 1.a No tiene propiedad
                                [
                                    'question_id' => 12,
                                    'operator' => '==',
                                    'value' => 0,
                                ],
                                // 1.b Tiene propiedad y justifica (Q13 ≠ "Ninguna de las anteriores")
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 12, 'operator' => '==', 'value' => 1],
                                        ['question_id' => 13, 'operator' => '!=',
                                            'value' => 'Ninguna de las anteriores'],
                                    ],
                                ],
                            ],
                        ],

                        // 2) Sub-norma convivientes
                        [
                            'condition' => 'OR',
                            'rules' => [
                                // 2.a No tienen propiedad
                                [
                                    'question_id' => 14,
                                    'operator' => '==',
                                    'value' => 0,
                                ],
                                // 2.b Tienen propiedad y justifican (Q15 ≠ "Ninguna de las anteriores")
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 14, 'operator' => '==', 'value' => 1],
                                        ['question_id' => 15, 'operator' => '!=',
                                            'value' => 'Ninguna de las anteriores'],
                                    ],
                                ],
                            ],
                        ],

                    ],
                ]),
            ],
            [
                'descripcion' => 'Exclusión por conviviente tener propiedades sin causa justificada',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Rama 1: Q14 == 0
                        [
                            'question_id' => 14,
                            'operator' => '==',
                            'value' => 0,
                        ],
                        // Rama 2: Q14 == 1 AND Q15 en la lista
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 14,
                                    'operator' => '==',
                                    'value' => 1,
                                ],
                                [
                                    'question_id' => 15,
                                    'operator' => 'in',
                                    'value' => [
                                        'Propiedad inaccesible por discapacidad tuya o de algún miembro de tu unidad de convivencia',
                                        'Propietario por herencia de una parte de la casa',
                                        'Separación o divorcio',
                                        'No puedes acceder a casa por cualquier causa ajena a tu voluntad',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No tener deudas con Hacienda y Seguridad Social',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 44, 'operator' => '==', 'value' => 0],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No tener parentesco con el arrendador',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 16, 'operator' => '==', 'value' => 0],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No haces los pagos por banco',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Caso 1: aún no hay contrato firmado → pasa automáticamente
                        [
                            'question_id' => 1,
                            'operator' => '==',
                            'value' => 'Todavía no tengo contrato de alquiler firmado.',
                        ],

                        // Caso 2: vivo en uno de los otros tipos y pago por banco
                        [
                            'condition' => 'AND',
                            'rules' => [
                                // Tipo de alquiler válido
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                        'Tengo un contrato por habitación.',
                                        'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                        'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                    ],
                                ],
                                // Y pago por banco (P18 == 1) O bien (P18 == 0 Y P19 == 1)
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 18, 'operator' => '==', 'value' => 1],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 18, 'operator' => '==', 'value' => 0],
                                                ['question_id' => 19, 'operator' => '==', 'value' => 1],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Límite de ingresos según unidad de convivencia PAV MADRID, ARAGÓN, CASTILLA Y LEÓN, GALICIA',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [ // Usuarios sin grupo vulnerable y vive solo
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8, 'operator' => '==', 'value' => 'Ninguna de las anteriores'],
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 4200],
                            ],
                        ],
                        [ // Usuarios sin grupo vulnerable y vive con convivientes
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8, 'operator' => '==', 'value' => 'Ninguna de las anteriores'],
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 4200],
                                ['question_id' => 28, 'operator' => '<=', 'value' => 25200],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 29, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 29, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 30, 'operator' => '==', 'value' => 0],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [  // Usuarios que viven solos y con grupo vulnerables = violencia de genero,familia numerosa especial y prestaciones agotadas
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
                                                ['question_id' => 11, 'operator' => 'in', 'value' => ['He sido víctima de violencia de género']],
                                            ],
                                        ],
                                        ['question_id' => 8, 'operator' => 'in', 'value' => ['Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones']],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Familia monoparental', 'Familia monoparental especial']],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 0],
                            ],
                        ],
                        [  // Usuarios que viven solos y con grupo vulnerables = minusvalía <33% y familia numerosa general
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
                                                ['question_id' => 11, 'operator' => 'in', 'value' => ['He sido víctima de terrorismo']],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Familia numerosa general']],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Persona con discapacidad reconocida inferior o igual al 33%']],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 33600],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 4200],
                            ],
                        ],
                        [  // Usuarios que viven solos y con grupo vulnerables = minusvalía >33% y familia numerosa especial
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Familia numerosa especial']],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Persona con discapacidad reconocida superior al 33%']],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 42000],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 4200],
                            ],
                        ],
                        [  // Usuarios que viven con convivientes y con grupo vulnerables = violencia de genero,familia numerosa especial y prestaciones agotadas
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
                                                ['question_id' => 11, 'operator' => 'in', 'value' => ['He sido víctima de violencia de género']],
                                            ],
                                        ],
                                        ['question_id' => 8, 'operator' => 'in', 'value' => ['Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones']],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Familia monoparental', 'Familia monoparental especial']],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 0],
                                ['question_id' => 28, 'operator' => '<=', 'value' => 25200],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 29, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 29, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 30, 'operator' => '==', 'value' => 0],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [  // Usuarios que viven con convivientes y con grupo vulnerables = minusvalía <33% y familia numerosa general
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
                                                ['question_id' => 11, 'operator' => 'in', 'value' => ['He sido víctima de terrorismo']],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Familia numerosa general']],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Persona con discapacidad reconocida inferior o igual al 33%']],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 33600],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 4200],
                                ['question_id' => 28, 'operator' => '<=', 'value' => 33600],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 29, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 29, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 30, 'operator' => '==', 'value' => 0],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [  // Usuarios que viven con convivientes y con grupo vulnerables = minusvalía >33% y familia numerosa especial
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Familia numerosa especial']],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Familia numerosa, monoparental, persona con discapacidad ±33%']],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => ['Persona con discapacidad reconocida superior al 33%']],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 42000],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 4200],
                                ['question_id' => 28, 'operator' => '<=', 'value' => 42000],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 29, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 29, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 30, 'operator' => '==', 'value' => 0],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],

            [
                'descripcion' => 'Precio del alquiler no supera el límite permitido en Aragón',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 600,
                        'habitacion' => 300,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'GENÉRICO',
                            'municipios' => [],
                            'piso_completo' => 600,
                            'habitacion' => 300,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                    ],
                    'ajustes_extra' => [
                        'Garaje' => 20,
                        'Trastero' => 5,
                        'Gastos de comunidad' => 5,
                    ],
                ]),
            ],
            [
                'descripcion' => 'Debes estar viviendo de alquiler actualmente',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'vives_alquiler')->first()->id, 'operator' => '==', 'value' => 1],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Ganar menos de 25.200€ al año',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'dinero_ganado')->first()->id, 'operator' => '<=', 'value' => 25200],
                    ],
                ]),
            ],
        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 16,
            'descripcion' => 'Requisitos para la ayuda de alquiler PAV Aragón',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    }
}
