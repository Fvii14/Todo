<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonPAVGaliciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requisitos = [
            [// Si la respuesta de la question_id 1 es igual a "Todavía no tiene contrato" no cumple
                'descripcion' => 'Debes tener contrato de alquiler firmado',
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

            // !!----Tener DNI o NIE-------------------------------------------------------------------------------------------------------

            [ // -Si la respuesta de la question_id 1 es igual a "Tengo un contrato por habitación." No cumple
                'descripcion' => 'No se admite contrato por habitación',
                'json_regla' => json_encode([
                    'question_id' => 1,
                    'operator' => '!=',
                    'value' => 'Tengo un contrato por habitación.',
                ]),
            ],
            [// -Cuando la answers de la question_id 1 es igual a 'Vivo de alquiler en una vivienda completa y vivo sola/o.' o 'Tengo un contrato por habitación.'
                //  Y la answers de la question_id 34 si es diferente a "" cumple

                // -Cuando la answers de la question_id 1 es diferente 'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.' o 'Vivo de alquiler en 	una vivienda y todas las personas forman parte del contrato.'
                //  Y la answers de la question_id 34 si es diferente a "" cumple Y la answers de la question_id 22 es igual a 1
                'descripcion' => 'Debe tener DNI o NIE válido en función del tipo de contrato',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Primer caso: vive solo o tiene contrato por habitación y tiene DNI/NIE
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
                                    'question_id' => 34,
                                    'operator' => '!=',
                                    'value' => '',
                                ],
                            ],
                        ],
                        // Segundo caso: otro tipo de alquiler, tiene DNI/NIE y es el titular (question 22 = 1)
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
                                [
                                    'question_id' => 34,
                                    'operator' => '!=',
                                    'value' => '',
                                ],
                                [
                                    'question_id' => 22,
                                    'operator' => '==',
                                    'value' => 1,
                                ],
                            ],
                        ],
                    ],
                ]),
            ],

            // !!---Ser mayor de edad-------------------------------------------------------------------------------------------------------
            // ___GENERAL___
            // -respuesta de la 'question_id' => 40 tiene que ser mayor de 18(la respuesta es la fecha de nacimiento supongo que debemos añadir un operador en la función compararValor que sea 	parecido al de less_than_years pero que sea greater_than o algo así).
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
            // __GENERAL__
            // -si la respuesta pera la  ['question_id' => 1, 'operator' => 'in', 'value' => [
            //                         'Vivo de alquiler en una vivienda completa y vivo sola/o.',
            //                         'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
            //                         'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.'
            //                     ]],
            // 		Y    ['question_id' => 2, 'operator' => '==', 'value' => 1] entonces cumple

            [
                'descripcion' => 'No tener contrato de alquiler de mínimo 12 meses',
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

            // !!---Estar empadronado en la vivienda en la que vive de alquiler---------------------------------------------------------------------------------------------------------------------------

            // ____GENERAL___
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
                                [
                                    'question_id' => 26,
                                    'operator' => '==',
                                    'value' => 0,
                                ],
                                [
                                    'question_id' => 27,
                                    'operator' => '==',
                                    'value' => 1,
                                ],
                            ],
                        ],
                    ],
                ]),
            ],

            // ____GENERAL____
            [
                'descripcion' => 'No tener propiedades (salvo excepciones justificadas)',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [

                        // Caso 1: contratos individuales o sin contrato
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
                                                ['question_id' => 15, 'operator' => '!=', 'value' => 'Ninguna de las anteriores'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Caso 2: contratos compartidos con convivientes
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
            // !! ---No tener deudas con Hacienda----------------------------------------------------------------------------------------------------------

            // ___GENERAL___
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

            // !!---No tener parentesco de 1-2º con el casero, o sean socios.------------------------------------------------------------------------------------
            // ___GENERAL___
            [
                'descripcion' => 'No tener parentesco con el arrendador',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 16, 'operator' => '==', 'value' => 0], // 0 = No tiene parentesco
                    ],
                ]),
            ],

            // !!---Pagar los recibos por transferencia bancaria.---------------------------------------------------------------------------------------------------

            // ___GENERAL___

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
                'descripcion' => 'Límite de ingresos según unidad de convivencia PAV MADRID, ARAGÓN, CASTILLA Y LEÓN, GALICIA (unificado)',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [

                        // 1. Sin grupo vulnerable – vive solo
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8,  'operator' => '==', 'value' => 'Ninguna de las anteriores'],
                                ['question_id' => 1,  'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                ['question_id' => 86, 'operator' => '>=', 'value' => 4200],
                            ],
                        ],

                        // 2. Sin grupo vulnerable – con convivientes
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8,  'operator' => '==', 'value' => 'Ninguna de las anteriores'],
                                ['question_id' => 1,  'operator' => 'in', 'value' => [
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

                        // 3. Vulnerables que viven solos – violencia de género, prestaciones agotadas, monoparental especial
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
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8,  'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
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

                        // 4. Vulnerables que viven solos – discapacidad ≤33%, terrorismo, familia numerosa general o inferior al 33%
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
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8,  'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
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

                        // 5. Vulnerables que viven solos – discapacidad >33% o familia numerosa especial
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

                        // 6. Vulnerables con convivientes – VG, prestaciones, monoparental especial
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
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8,  'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
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

                        // 7. Vulnerables con convivientes – discapacidad ≤33%, terrorismo, familia numerosa general o inferior
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
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8,  'operator' => 'in', 'value' => ['Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a']],
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

                        // 8. Vulnerables con convivientes – discapacidad >33% o familia numerosa especial
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
                ], JSON_UNESCAPED_UNICODE),
            ],

            [
                'descripcion' => 'Precio del alquiler no supera el límite permitido en Galicia',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 425,
                        'habitacion' => 212.5,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'Ciudades principales Galicia',
                            'municipios' => [
                                'A Coruña', 'Santiago de Compostela', 'Pontevedra', 'Vigo', 'Lugo', 'Ourense', 'Ferrol',
                            ],
                            'piso_completo' => 600,
                            'habitacion' => 300,
                            'familia_numerosa_general' => 720,
                        ],
                        [
                            'nombre' => 'Zonas intermedias Galicia',
                            'municipios' => [
                                'Ames', 'Ares', 'Arteixo', 'Pontes de García Rodríguez, As', 'Betanzos', 'Boiro', 'Cambre', 'Carballo', 'Cee', 'Cedeira', 'Culleredo', 'Fene',
                                'Melide', 'Mugardos', 'Narón', 'Neda', 'Noia', 'Oleiros', 'Ordes', 'Oroso', 'Padrón', 'Pontedeume', 'Ribeira', 'Sada', 'Teo',
                                'Burela', 'Cervo', 'Chantada', 'Foz', 'Monforte de Lemos', 'Ribadeo', 'Sarria', 'Vilalba', 'Viveiro',
                                'Allariz', 'Rúa, A', 'Barco de Valdeorras, O', 'Carballiño, O', 'Celanova', 'Ribadavia', 'Verín', 'Xinzo de Limia',
                                'Estrada, A', 'Illa de Arousa, A', 'Baiona', 'Bueu', 'Cambados', 'Cangas', 'Gondomar', 'Lalín', 'Marín', 'Moaña', 'Mos', 'Nigrán',
                                'Grove, O', 'Porriño, O', 'Poio', 'Ponteareas', 'Pontecesures', 'Redondela', 'Sanxenxo', 'Tui', 'Vilagarcía de Arousa', 'Vilanova de Arousa',
                            ],
                            'piso_completo' => 550,
                            'habitacion' => 275,
                            'familia_numerosa_general' => 660,
                        ],
                        [
                            'nombre' => 'Resto de Galicia',
                            'municipios' => [],
                            'piso_completo' => 425,
                            'habitacion' => 212.5,
                            'familia_numerosa_general' => 510,
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
                'descripcion' => 'Exclusión por tener propiedades sin causa justificada',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Rama 1: Q14 == 0
                        [
                            'question_id' => 47,
                            'operator' => '==',
                            'value' => 0,
                        ],
                        // Rama 2: Q14 == 1 AND Q15 en la lista
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 47,
                                    'operator' => '==',
                                    'value' => 1,
                                ],
                                [
                                    'question_id' => 13,
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

            // AQUÍ TERMINA EL JSON AÑADIDO
        ];
        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 32, // ID de la ayuda PAV Galicia
            'descripcion' => 'Requisitos PAV Galicia',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
