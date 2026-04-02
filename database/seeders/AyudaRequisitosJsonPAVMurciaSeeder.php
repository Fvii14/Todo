<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonPAVMurciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requisitos = [

            [
                // - Si el usuario es menor de 36 años puede no tener contrato y presentarlo
                // 	Si la respuesta a 'question_id' => 1,
                //                                 'operator' => 'in',
                //                                 'value' => ["Todavía no tiene contrato"]
                // 	AND Si 'question_id' => 40, 'operator' => 'less_than_years', 'value' => 36
                // 	AND la respuesta de la question_id 1 es igual a "Todavía no tiene contrato" cumple

                'descripcion' => 'El solicitante puede no tener contrato solo si es menor de 36 años',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 1,
                                    'operator' => '==',
                                    'value' => 'Todavía no tengo contrato de alquiler firmado.',
                                ],
                                [
                                    'question_id' => 40,
                                    'operator' => 'less_than_years',
                                    'value' => 36,
                                ],
                            ],
                        ],
                        [
                            'question_id' => 1,
                            'operator' => '!=',
                            'value' => 'Todavía no tengo contrato de alquiler firmado.',
                        ],
                    ],
                ]),
            ],
            // !!----Tener DNI o NIE-------------------------------------------------------------------------------------------------------

            // ___GENERAL___
            [           // -Cuando la answers de la question_id 1 es igual a 'Vivo de alquiler en una vivienda completa y vivo sola/o.' o 'Tengo un contrato por habitación.'
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

            [
                'descripcion' => 'Contrato válido o excepción por menor de 36 años sin contrato en Madrid, Murcia y Asturias',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [

                        // Opción 1: menor de 36 años y sin contrato
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 40,
                                    'operator' => 'less_than_years',
                                    'value' => 36,
                                ],
                                [
                                    'question_id' => 1,
                                    'operator' => '==',
                                    'value' => 'Todavía no tiene contrato',
                                ],
                            ],
                        ],

                        // Opción 2: contrato válido con duración mínima
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
                'descripcion' => 'Estar empadronado en la vivienda de alquiler (Madrid, Murcia, Asturias, La Rioja)',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => 40,
                            'operator' => 'less_than_years',
                            'value' => 36,
                        ],
                        [
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
                        ],
                    ],
                ]),
            ],

            // !!---NO tener propiedades --------------------------------------------------------------------------------------------------------------
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
                'descripcion' => 'Exclusión por tener propiedades sin causa justificada',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // Rama 1: Q14 == 0
                        [
                            'question_id' => 12,
                            'operator' => '==',
                            'value' => 0,
                        ],
                        // Rama 2: Q14 == 1 AND Q15 en la lista
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'question_id' => 12,
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
            // !!!-----------------Pagar como máximo los siguientes importes por su alquiler, iguales que en la tabla del-----------------------------------------------------------------------------------

            // ___GENERAL___
            [
                'descripcion' => 'Precio del alquiler no supera el límite permitido GENERICO',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 600,
                        'habitacion' => 300,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'GENRICO',
                            'municipios' => [], // Se aplica a todos
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
            'ayuda_id' => 26, // ID de la ayuda PAV Murcia
            'descripcion' => 'Requisitos PAV Murcia',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
