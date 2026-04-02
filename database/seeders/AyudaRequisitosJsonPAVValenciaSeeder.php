<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonPAVValenciaSeeder extends Seeder
{
    public function run(): void
    {
        $requisitos = [
            [ // Si la respuesta de la question_id 1 es igual a "Todavía no tiene contrato" no cumple
                'descripcion' => 'El solicitante debe tener contrato de alquiler firmado',
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
                                    'question_id' => 60,
                                    'operator' => '==',
                                    'value' => '1',
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
                                    'question_id' => 60,
                                    'operator' => '==',
                                    'value' => '1',
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
                                        'Tengo un contrato por habitación.',

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
                                        ['question_id' => 12, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 12, 'operator' => '==', 'value' => 1],
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
                                        ['question_id' => 12, 'operator' => '==', 'value' => 0],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 12, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 13, 'operator' => '==', 'value' => 'Ninguna de las anteriores'],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 12, 'operator' => '==', 'value' => 1],
                                                ['question_id' => 14, 'operator' => '==', 'value' => 0],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 12, 'operator' => '==', 'value' => 1],
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
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                ['question_id' => 44, 'operator' => '==', 'value' => 1],
                                // todo ver porque está esta pregunta 172
                                ['question_id' => 172, 'operator' => '==', 'value' => 0],
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
                'descripcion' => 'No tener parentesco con el arrendador',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 16, 'operator' => '==', 'value' => 0], // 0 = No tiene parentesco
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
                'descripcion' => 'No se cumple los límites de ingresos',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [

                        // 1️⃣ PERSONA SIN GRUPO VULNERABLE Y VIVE SOLA
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Ninguna de las anteriores']],
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                // Máximo ingresos
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '<=', 'value' => 25200],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                            ],
                                        ],
                                    ],
                                ],
                                // Mínimo ingresos
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // 2️⃣ FAMILIA NUMEROSA O DISCAPACIDAD <33% VIVE SOLO
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                ['question_id' => 8, 'operator' => '==', 'value' => 'Familia numerosa, monoparental, persona con discapacidad \u00b133%'],
                                ['question_id' => 9, 'operator' => 'in', 'value' => [
                                    'Persona con discapacidad reconocida inferior o igual al 33%',
                                    'Familia numerosa general',
                                ]],
                                // Máximo ingresos
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '<=', 'value' => 33600],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '<=', 'value' => 33600],
                                            ],
                                        ],
                                    ],
                                ],
                                // Mínimo ingresos
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // 3️⃣ VÍCTIMA DE TERRORISMO VIVE SOLA
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                ['question_id' => 8, 'operator' => '==', 'value' => 'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a'],
                                ['question_id' => 11, 'operator' => '==', 'value' => 'He sido víctima de terrorismo'],
                                // Máximo ingresos
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '<=', 'value' => 33600],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '<=', 'value' => 33600],
                                            ],
                                        ],
                                    ],
                                ],
                                // Mínimo ingresos
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // 4️⃣ FAMILIA NUMEROSA O DISCAPACIDAD <33% CON CONVIVIENTES
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
                                                ['question_id' => 8, 'operator' => '==', 'value' => 'Familia numerosa, monoparental, persona con discapacidad \u00b133%'],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => [
                                                    'Familia numerosa',
                                                    'Persona con discapacidad reconocida inferior o igual al 33%',
                                                    'Familia monoparental',
                                                ]],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => '==', 'value' => 'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a'],
                                                ['question_id' => 11, 'operator' => '==', 'value' => 'He sido víctima de terrorismo'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Máximo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '<=', 'value' => 33600],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '<=', 'value' => 33600],
                                            ],
                                        ],
                                    ],
                                ],
                                // Máximo ingresos unidad convivencia
                                ['question_id' => 28, 'operator' => '<=', 'value' => 33600],
                                // Mínimo ingresos unidad convivencia
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 28, 'operator' => '>=', 'value' => 2520],
                                        ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                    ],
                                ],
                                // Mínimo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // 5️⃣ FAMILIAS NUMEROSAS ESPECIAL O DISCAPACIDAD ≥33% VIVE SOLO
                        [
                            'condition' => 'AND',
                            'rules' => [
                                // Tipo de alquiler individual
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                // Grupo de colectivos
                                ['question_id' => 8, 'operator' => 'in', 'value' => [
                                    'Familia numerosa, monoparental, persona con discapacidad =>33%',
                                    'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
                                ]],
                                // Condición extra
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 9, 'operator' => '==', 'value' => 'Persona con discapacidad reconocida superior al 33%'],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => '==', 'value' => 'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a'],
                                                ['question_id' => 11, 'operator' => '==', 'value' => 'He sido víctima de violencia de género'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Máximo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '<=', 'value' => 42000],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '<=', 'value' => 42000],
                                            ],
                                        ],
                                    ],
                                ],
                                // Mínimo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // 6️⃣ FAMILIAS NUMEROSAS ESPECIAL O DISCAPACIDAD ≥33% CON CONVIVIENTES
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
                                                ['question_id' => 8, 'operator' => '==', 'value' => 'Familia numerosa, monoparental, persona con discapacidad \u00b133%'],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => [
                                                    'Familia numerosa especial',
                                                    'Familia monoparental especial',
                                                    'Persona con discapacidad reconocida superior al 33%',
                                                ]],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => '==', 'value' => 'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a'],
                                                ['question_id' => 11, 'operator' => '==', 'value' => 'He sido víctima de terrorismo'],
                                            ],
                                        ],
                                    ],
                                ],
                                // Máximo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '<=', 'value' => 42000],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '<=', 'value' => 42000],
                                            ],
                                        ],
                                    ],
                                ],
                                // Máximo ingresos unidad convivencia
                                ['question_id' => 28, 'operator' => '<=', 'value' => 42000],
                                // Mínimo ingresos unidad convivencia
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 28, 'operator' => '>=', 'value' => 2520],
                                        ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                    ],
                                ],
                                // Mínimo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // 7️⃣ PERSONA SIN GRUPO CON CONVIVIENTES
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8, 'operator' => 'in', 'value' => ['Ninguna de las anteriores']],
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                // Máximo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '<=', 'value' => 25200],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                            ],
                                        ],
                                    ],
                                ],
                                // Máximo ingresos unidad convivencia
                                ['question_id' => 28, 'operator' => '<=', 'value' => 25200],
                                // Mínimo ingresos unidad convivencia
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        ['question_id' => 28, 'operator' => '>=', 'value' => 2520],
                                        ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                    ],
                                ],
                                // Mínimo ingresos individuales
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '!=', 'value' => null],
                                                ['question_id' => 86, 'operator' => '>=', 'value' => 2520],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 86, 'operator' => '==', 'value' => null],
                                                ['question_id' => 43, 'operator' => '>=', 'value' => 2520],
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
                'descripcion' => 'Precio del alquiler no supera el límite permitido según municipio y tipo de alquiler',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 650,
                        'habitacion' => 300,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'Valencia capital',
                            'municipios' => ['València'],
                            'piso_completo' => 900,
                            'habitacion' => 450,
                        ],
                        [
                            'nombre' => 'Alicante y Castellón capital',
                            'municipios' => ['Alacant/Alicante', 'Castelló de la Plana/Castellón de la Plana'],
                            'piso_completo' => 800,
                            'habitacion' => 400,
                        ],
                        [
                            'nombre' => 'Afectados DANA',
                            'municipios' => [
                                'Alaquàs',
                                'Albal',
                                'Albalat de la Ribera',
                                'Alberic',
                                'Alborache',
                                'Alcàsser',
                                'Alcúdia, l\'',
                                'Aldaia',
                                'Alfafar',
                                'Alfarb',
                                'Algemesí',
                                'Alginet',
                                'Almussafes',
                                'Alzira',
                                'Aras de los Olmos',
                                'Barxeta',
                                'Benagéber',
                                'Benaguasil',
                                'Benetússer',
                                'Benicull de Xúquer',
                                'Benifaió',
                                'Benimodo',
                                'Benimuslem',
                                'Beniparrell',
                                'Bétera',
                                'Bugarra',
                                'Buñol',
                                'Calles',
                                'Camporrobles',
                                'Carlet',
                                'Casinos',
                                'Pedralba',
                                'Castelló',
                                'Castielfabib',
                                'Catadau',
                                'Catarroja',
                                'Caudete de las Fuentes',
                                'Chelva',
                                'Chulilla',
                                'Corbera',
                                'Cullera',
                                'Dos Aguas',
                                'Énova, l\'',
                                'Favara',
                                'Fortaleny',
                                'Fuenterrobles',
                                'Gavarda',
                                'Gestalgar',
                                'Godelleta',
                                'Guadassuar',
                                'La Pobla Llarga',
                                'Llaurí',
                                'Llíria',
                                'Llocnou de la Corona',
                                'Llombai',
                                'Loriguilla',
                                'Losa del Obispo',
                                'Manises',
                                'Macastre',
                                'Manuel',
                                'Massalavés',
                                'Massanassa',
                                'Millares',
                                'Mislata',
                                'Montroi/Montroy',
                                'Montserrat',
                                'Paiporta',
                                'Paterna',
                                'Picanya',
                                'Picassent',
                                'Polinyà de Xúquer',
                                'Quart de Poblet',
                                'Rafelguaraf',
                                'Real',
                                'Requena',
                                'Riba-roja de Túria',
                                'Riola',
                                'Sant Joanet',
                                'Sedaví',
                                'Senyera',
                                'Siete Aguas',
                                'Silla',
                                'Sinarcas',
                                'Sollana',
                                'Sot de Chera',
                                'Sueca',
                                'Tavernes de la Valldigna',
                                'Titaguas',
                                'Torrent',
                                'Tous',
                                'Tuéjar',
                                'Turís',
                                'Utiel',
                                'Vilamarxant',
                                'Villar del Arzobispo',
                                'Xeraco',
                                'Xirivella',
                                'Yátova',
                                'Carcaixent',
                                'Cheste',
                                'Chera',
                                'Chiva',
                            ],
                            'piso_completo' => 800,
                            'habitacion' => 400,
                        ],
                        [
                            'nombre' => 'Ciudades medias',
                            'municipios' => ['Gandia', 'Sagunt/Sagunto', 'Elx/Elche', 'Benidorm', 'Borriana/Burriana', 'Benicàssim/Benicasim', 'Vila-real', 'Campello, el', 'Mutxamel', 'Sant Joan d\'Alacant', 'Sant Vicent del Raspeig/San Vicente del Raspeig'],
                            'piso_completo' => 700,
                            'habitacion' => 350,
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
                'descripcion' => 'Ganar menos de 42.000€ al año',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'dinero_ganado')->first()->id, 'operator' => '<=', 'value' => 42000],
                    ],
                ]),
            ],
        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 7,
            'descripcion' => 'Requisitos para la Ayuda al Alquiler PAV Valencia',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
