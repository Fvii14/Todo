<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonBAJMadridSeeder extends Seeder
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
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                ]],
                                ['question_id' => 34, 'operator' => '!=', 'value' => ''],
                            ],
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                ]],
                                ['question_id' => 34, 'operator' => '!=', 'value' => ''],
                                ['question_id' => 22, 'operator' => '==', 'value' => 1],
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
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 47, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 47, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 15, 'operator' => '!=', 'value' => 'Ninguna de las anteriores'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
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
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 1, 'operator' => 'in', 'value' => [
                            'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                            'Tengo un contrato por habitación.',
                            'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                            'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                        ]],
                        ['question_id' => 2, 'operator' => '==', 'value' => 1],
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
                'descripcion' => 'Pago por banco',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 1, 'operator' => 'in', 'value' => [
                            'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                            'Tengo un contrato por habitación.',
                            'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                            'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                        ]],
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
                'descripcion' => 'Precio alquiler no supera el límite',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 600,
                        'habitacion' => 300,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'Municipios alta demanda Madrid',
                            'municipios' => [
                                'Ajalvir',
                                'Alcalá de Henares',
                                'Alcobendas',
                                'Alcorcón',
                                'Algete',
                                'Alpedrete',
                                'Aranjuez',
                                'Arganda del Rey',
                                'Arroyomolinos',
                                'Boadilla del Monte',
                                'Boalo, El',
                                'Brunete',
                                'Camarma de Esteruelas',
                                'Ciempozuelos',
                                'Cobeña',
                                'Collado Villalba',
                                'Colmenar Viejo',
                                'Colmenarejo',
                                'Coslada',
                                'Daganzo de Arriba',
                                'Escorial, El',
                                'Fuenlabrada',
                                'Fuente el Saz de Jarama',
                                'Galapagar',
                                'Getafe',
                                'Guadarrama',
                                'Humanes de Madrid',
                                'Rozas de Madrid, Las',
                                'Leganés',
                                'Madrid',
                                'Majadahonda',
                                'Mejorada del Campo',
                                'Moraleja de Enmedio',
                                'Moralzarzal',
                                'Móstoles',
                                'Navalcarnero',
                                'Paracuellos del Jarama',
                                'Parla',
                                'Pinto',
                                'Pozuelo de Alarcón',
                                'Rivas Vaciamadrid',
                                'San Agustín del Guadalix',
                                'San Fernando de Henares',
                                'San Lorenzo de El Escorial',
                                'San Martín de la Vega',
                                'San Sebastián de los Reyes',
                                'Soto del Real',
                                'Torrejón de Ardoz',
                                'Torrelodones',
                                'Tres Cantos',
                                'Valdemoro',
                                'Velilla de San Antonio',
                                'Villanueva de la Cañada',
                                'Villanueva del Pardillo',
                                'Villaviciosa de Odón',
                            ],
                            'piso_completo' => 900,
                            'habitacion' => 450,
                        ],
                    ],
                    'ajustes_extra' => [
                        'Garaje' => 20,
                        'Trastero' => 5,
                        'Gastos de comunidad' => 5,
                    ],
                ]),
            ],
        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 29, // ID de la ayuda BAJ COMUNIDAD Madrid
            'descripcion' => 'Requisitos BAJ Madirid',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
