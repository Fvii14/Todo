<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonBAJAragon extends Seeder
{
    public function run(): void
    {
        $ayudaIds = [17];
        $requisitos = [
            [
                'descripcion' => 'Tener menos de 36 años',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 40, 'operator' => 'less_than_years', 'value' => 36],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Tener DNI o NIE',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                        'Tengo un contrato por habitación.',
                                    ],
                                ],
                                ['question_id' => 34, 'operator' => '!=', 'value' => ''],
                            ],
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                        'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    ],
                                ],
                                ['question_id' => 34, 'operator' => '!=', 'value' => ''],
                                ['question_id' => 22, 'operator' => '==', 'value' => 1],
                            ],
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Todavía no tengo contrato de alquiler firmado.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Fuente regular de ingresos',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 46, 'operator' => '!=', 'value' => 0],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No tener propiedades (salvo excepciones justificadas)',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                        'Tengo un contrato por habitación.',
                                        'Todavía no tengo contrato de alquiler firmado.',
                                    ],
                                ],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 47, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 47, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 13, 'operator' => '!=', 'value' => 'Ninguna de las anteriores'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                        'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                    ],
                                ],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 47, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 47, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 13, 'operator' => '==', 'value' => 'Ninguna de las anteriores'],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 47, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 14, 'operator' => '==', 'value' => 0],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 47, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 14, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 15, 'operator' => '==', 'value' => 'Ninguna de las anteriores'],
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
                'descripcion' => 'No tener deudas con Hacienda y Seguridad Social',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                        'Tengo un contrato por habitación.',
                                        'Todavía no tengo contrato de alquiler firmado.',
                                    ],
                                ],
                                // ['question_id' => 44, 'operator' => '==', 'value' => 0]
                            ],
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                        'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                    ],
                                ],
                                // ['question_id' => 44, 'operator' => '==', 'value' => 0],
                                // [
                                //     'condition' => 'OR',
                                //     'rules' => [
                                //         ['question_id' => 20, 'operator' => '==', 'value' => 0],
                                //         [
                                //             'condition' => 'AND',
                                //             'rules' => [
                                //                 ['question_id' => 20, 'operator' => '==', 'value' => 1],
                                //                 ['question_id' => 21, 'operator' => '==', 'value' => 0]
                                //             ]
                                //         ]
                                //     ]
                                // ]
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Contrato de alquiler mínimo 12 meses',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Caso 1: aún no hay contrato firmado → pasa
                        [
                            'question_id' => 1,
                            'operator' => '==',
                            'value' => 'Todavía no tengo contrato de alquiler firmado.',
                        ],
                        // Caso 2: vivo en uno de los otros tipos y P2==1 → pasa
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                        'Tengo un contrato por habitación.',
                                        'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                        'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    ],
                                ],
                                [
                                    'question_id' => 2,
                                    'operator' => '==',
                                    'value' => 1,
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Estar empadronado en la vivienda alquilada (con excepciones)',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'question_id' => 1,
                            'operator' => '==',
                            'value' => 'Todavía no tengo contrato de alquiler firmado.',
                        ],
                        [
                            'condition' => 'OR',
                            'rules' => [
                                ['question_id' => 26, 'operator' => '==', 'value' => 1],
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 26, 'operator' => '==', 'value' => 0],
                                        ['question_id' => 27, 'operator' => '==', 'value' => 1],
                                    ],
                                ],
                            ],
                        ],
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
                'descripcion' => 'Pago por banco',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Caso 1: aún no hay contrato firmado → pasa automáticamente
                        [
                            'question_id' => 1,
                            'operator' => '==',
                            'value' => 'Todavía no tengo contrato de alquiler firmado.',
                        ],

                        // Caso 2: vivo en uno de los otros tipos → sin tener en cuenta cómo paga
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
                    ],
                ]),
            ],
            [
                'descripcion' => 'Límite de ingresos individuales (máximo 25.200 €)',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Límite de ingresos convivientes (máximo 25.200 €)',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Caso 1: Q29 == 0
                        [
                            'question_id' => 29,
                            'operator' => '==',
                            'value' => 0,
                        ],
                        // Caso 2: Q29 == 1 Y Q30 == 0
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 29,
                                    'operator' => '==',
                                    'value' => 1,
                                ],
                                [
                                    'question_id' => 30,
                                    'operator' => '==',
                                    'value' => 0,
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Precio alquiler supera el límite establecido',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 600,
                        'habitacion' => 300,
                    ],
                    'familia_numerosa_general' => 900,
                    'familia_numerosa_especial' => 900,
                    'ajustes_extra' => [
                        'Garaje' => 15,
                        'Trastero' => 15,
                        'Gastos de comunidad' => 15,
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
                                        [
                                            'question_id' => 13,
                                            'operator' => '!=',
                                            'value' => 'Ninguna de las anteriores',
                                        ],
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
                                        [
                                            'question_id' => 15,
                                            'operator' => '!=',
                                            'value' => 'Ninguna de las anteriores',
                                        ],
                                    ],
                                ],
                            ],
                        ],

                    ],
                ]),
            ],

        ];

        foreach ($ayudaIds as $ayudaId) {
            $ayuda = DB::table('ayudas')->where('id', $ayudaId)->first();

            $requisitosFiltrados = $requisitos;
            if ($ayudaId == 17) {
                $requisitosFiltrados = array_filter($requisitosFiltrados, function ($req) {
                    return $req['descripcion'] !== 'No tener deudas con Hacienda y Seguridad Social';
                });
                $requisitosFiltrados = array_values($requisitosFiltrados);
            }

            DB::table('ayuda_requisitos_json')->insert([
                'ayuda_id' => $ayudaId,
                'descripcion' => 'Requisitos BAJ para '.($ayuda->nombre_ayuda),
                'json_regla' => json_encode($requisitosFiltrados),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
