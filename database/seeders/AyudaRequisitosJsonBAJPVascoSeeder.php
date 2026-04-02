<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonBAJPVascoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
            [ // Pregunta nueva sobre si esta recibiendo la RGI
                'descripcion' => ' ¿Estás recibiendo la Renta de Garantía de Ingresos (RGI) o alguna otra ayuda económica relacionada con la administración pública?',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 95, 'operator' => '==', 'value' => 0],
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
                                [
                                    'question_id' => 26,
                                    'operator' => '==',
                                    'value' => 1,
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
                'descripcion' => 'Límite de ingresos según unidad de convivencia',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Caso 1: Vive sola/o → ingresos <= 22000
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 5, 'operator' => '==', 'value' => 1],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 24500],
                            ],
                        ],
                        // Caso 2: Más de 1 conviviente → ingresos <= 30000
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 5, 'operator' => '>', 'value' => 1],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 30000],
                            ],
                        ],
                        // Caso 3: Familia numerosa → ingresos <= 32000
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8, 'operator' => 'in', 'value' => [
                                    'Familia numerosa',
                                ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 32000],
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
                'descripcion' => 'Precio alquiler no supera el límite',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 675,
                        'habitacion' => 335,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'Municipios alta demanda Pais Vasco',
                            'municipios' => [
                                'Bilbao',
                                'Donostia/San Sebastián',
                                'Vitoria-Gasteiz',

                            ],
                            'piso_completo' => 800,
                            'habitacion' => 400,
                        ],
                        [
                            'nombre' => 'Municipios media demanda Pais Vasco',
                            'municipios' => [
                                'Abanto y Ciérvana-Abanto Zierbena',
                                'Alonsotegi',
                                'Arrankudiaga-Zollo',
                                'Arrigorriaga',
                                'Barakaldo',
                                'Barrika',
                                'Basauri',
                                'Berango',
                                'Zamudio',
                                'Zierbena',
                                'Derio',
                                'Etxebarri',
                                'Erandio',
                                'Galdakao',
                                'Gorliz',
                                'Getxo',
                                'Larrabetzu',
                                'Leioa',
                                'Lemoiz',
                                'Lezama',
                                'Loiu',
                                'Ugao-Miraballes',
                                'Muskiz',
                                'Ortuella',
                                'Plentzia',
                                'Portugalete',
                                'Santurtzi',
                                'Sestao',
                                'Sondika',
                                'Sopela',
                                'Urduliz',
                                'Valle de Trápaga-Trapagaran',
                                'Zamudio',
                                'Zaratamo',
                                'Andoain',
                                'Astigarraga',
                                'Errenteria',
                                'Hernani',
                                'Hondarribia',
                                'Irun',
                                'Lasarte-Oria',
                                'Lezo',
                                'Oiartzun',
                                'Pasaia',
                                'Urnieta',
                                'Usurbil',
                                'Laudio/Llodio',
                                'Amurrio',
                                'Bilbao',
                                'Barakaldo',
                                'Getxo',
                                'Portugalete',
                                'Santurtzi',
                                'Basauri',
                                'Erandio',
                                'Leioa',
                                'Durango',
                                'Galdakao',
                                'Sestao',
                                'Irun',
                                'Errenteria',
                                'Eibar',
                                'Zarautz',
                                'Arrasate/Mondragón',
                                'Hernani',
                                'Tolosa',
                                'Beasain',
                                'Azpeitia',
                                'Elgoibar',
                                'Lasarte-Oria',
                                'Bergara',
                                'Zumaia',
                                'Zarautz',
                                'Azkoitia',
                                'Oñati',
                                'Ordizia',
                                'Urretxu',
                                'Zumarraga',

                            ],
                            'piso_completo' => 750,
                            'habitacion' => 375,
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
            'ayuda_id' => 35, // ID de la ayuda BAJ Pais Vasco
            'descripcion' => 'Requisitos BAJ PVasco',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
