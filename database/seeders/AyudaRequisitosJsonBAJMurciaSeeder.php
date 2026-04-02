<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonBAJMurciaSeeder extends Seeder
{
    public function run(): void
    {
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
                'descripcion' => 'No tener deudas con Hacienda y Seguridad Social',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                ['question_id' => 44, 'operator' => '==', 'value' => 0],
                            ],
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                ['question_id' => 44, 'operator' => '==', 'value' => 0],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 20, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 20, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 21, 'operator' => '==', 'value' => 0],
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
                'descripcion' => 'No ha solicitado otro miembro de la unidad de convivencia la ayuda',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        /* Pendiente de añadir pregunta */ ['question_id' => null, 'operator' => '==', 'value' => 0],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Coexistencia permitida si todos los convivientes < 36 y sin parentesco',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            // Caso: NO está en tipos con convivencia → no se aplica la condición
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => 'not_in',
                                    'value' => [
                                        'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                        'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            // Caso: SÍ está en tipos con convivencia → se evalúa si los convivientes cumplen
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
                                    'question_id' => 6,
                                    'operator' => '==',
                                    'value' => 1,
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Límite de ingresos según unidad de convivencia (Madrid)',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Tipos A, B, C → sólo ingresos del solicitante
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                            ],
                        ],
                        // Tipos D y E → ingresos de la unidad, pero sin regla de IPREM múltiple
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                ['question_id' => 29, 'operator' => '==', 'value' => 0],
                                ['question_id' => 30, 'operator' => '==', 'value' => 0],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No tener propiedades (salvo excepciones justificadas)',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        // 1) Solicitante: o no tiene (12 == 0), o tiene y justifica (12 == 1 && 13 != "Ninguna de las anteriores")
                        [
                            'condition' => 'OR',
                            'rules' => [
                                ['question_id' => 12, 'operator' => '==', 'value' => 0],
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 12, 'operator' => '==', 'value' => 1],
                                        ['question_id' => 13, 'operator' => '!=', 'value' => 'Ninguna de las anteriores'],
                                    ],
                                ],
                            ],
                        ],
                        // 2) Convivientes: o no tienen (14 == 0), o tienen y justifican (14 == 1 && 15 != "Ninguna de las anteriores")
                        [
                            'condition' => 'OR',
                            'rules' => [
                                ['question_id' => 14, 'operator' => '==', 'value' => 0],
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 14, 'operator' => '==', 'value' => 1],
                                        ['question_id' => 15, 'operator' => '!=', 'value' => 'Ninguna de las anteriores'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],

            [
                'descripcion' => 'Precio alquiler no supera el límite',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 600,
                        'habitacion' => 300,
                    ],
                    'ajustes_extra' => [
                        'Garaje' => 20,
                        'Trastero' => 5,
                        'Gastos de comunidad' => 2,
                    ],
                ]),
            ],
        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 27, // ID de la ayuda BAJ Murcia
            'descripcion' => 'Requisitos BAJ Murcia',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
