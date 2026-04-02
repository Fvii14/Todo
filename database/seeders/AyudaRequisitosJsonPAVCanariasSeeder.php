<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonPAVCanariasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
                'descripcion' => 'Límite de ingresos según unidad de convivencia PAV CANARIAS, ASTURIAS, ISLAS BALEARES, CANTABRIA, EXTREMADURA, LA RIOJA, CASTILLA LA MANCHA',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        // 1) Usuarios sin grupo vulnerable y vive solo
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
                            ],
                        ],

                        // 2) Usuarios sin grupo vulnerable y vive con convivientes
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 8,  'operator' => '==', 'value' => 'Ninguna de las anteriores'],
                                ['question_id' => 1,  'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
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

                        // 3) Usuarios que viven solos y con grupo vulnerable (minusvalía <33%, terrorismo, familia numerosa general)
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1,  'operator' => 'in', 'value' => [
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
                                                ['question_id' => 8,  'operator' => 'in', 'value' => [
                                                    'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
                                                ]],
                                                ['question_id' => 11, 'operator' => 'in', 'value' => [
                                                    'He sido víctima de terrorismo',
                                                ]],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => [
                                                    'Familia numerosa, monoparental, persona con discapacidad ±33%',
                                                ]],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => [
                                                    'Familia numerosa general',
                                                ]],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 33600],
                            ],
                        ],

                        // 4) Usuarios que viven solos y con grupo vulnerable (minusvalía >33%, familia numerosa especial)
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1,  'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 8, 'operator' => 'in', 'value' => [
                                            'Familia numerosa, monoparental, persona con discapacidad ±33%',
                                        ]],
                                        ['question_id' => 9, 'operator' => 'in', 'value' => [
                                            'Familia numerosa especial',
                                        ]],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 42000],
                            ],
                        ],

                        // 5) Usuarios que viven solos y con violencia de género/exclusión/extutelados/exconvictos
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1,  'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                                    'Tengo un contrato por habitación.',
                                    'Todavía no tengo contrato de alquiler firmado.',
                                ]],
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 8,  'operator' => 'in', 'value' => [
                                            'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
                                        ]],
                                        ['question_id' => 11, 'operator' => 'in', 'value' => [
                                            'He sido víctima de violencia de género',
                                            'Estoy en riesgo de exclusión social',
                                            'Soy joven extutelado/a',
                                            'He estado en prisión (exconvicto/a)',
                                        ]],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                            ],
                        ],

                        // 6) Usuarios que viven solos y con supuestos especiales
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
                                            'question_id' => 8,
                                            'operator' => 'in',
                                            'value' => [
                                                'Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones',
                                            ],
                                        ],
                                        [
                                            'question_id' => 8,
                                            'operator' => 'in',
                                            'value' => [
                                                'Personas que vivan solas cuyos ingresos proceden de subsidios y no superen anualmente los 8.106,28€',
                                            ],
                                        ],
                                        [
                                            'question_id' => 8,
                                            'operator' => 'in',
                                            'value' => [
                                                'Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado/a por situación catastrófica',
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                            ],
                        ],

                        // 7) Usuarios que viven con convivientes y con grupo vulnerable (minusvalía <33%, terrorismo, familia numerosa general)
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
                                                ['question_id' => 8,  'operator' => 'in', 'value' => [
                                                    'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
                                                ]],
                                                ['question_id' => 11, 'operator' => 'in', 'value' => [
                                                    'He sido víctima de terrorismo',
                                                ]],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 8, 'operator' => 'in', 'value' => [
                                                    'Familia numerosa, monoparental, persona con discapacidad ±33%',
                                                ]],
                                                ['question_id' => 9, 'operator' => 'in', 'value' => [
                                                    'Familia numerosa general',
                                                ]],
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 33600],
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

                        // 8) Usuarios que viven con convivientes y con grupo vulnerable (minusvalía >33%, familia numerosa especial)
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 8, 'operator' => 'in', 'value' => [
                                            'Familia numerosa, monoparental, persona con discapacidad ±33%',
                                        ]],
                                        ['question_id' => 9, 'operator' => 'in', 'value' => [
                                            'Familia numerosa especial',
                                        ]],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 42000],
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

                        // 9) Usuarios que viven con convivientes y con violencia de género/exclusión/extutelados/exconvictos
                        [
                            'condition' => 'AND',
                            'rules' => [
                                ['question_id' => 1, 'operator' => 'in', 'value' => [
                                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                                ]],
                                [
                                    'condition' => 'AND',
                                    'rules' => [
                                        ['question_id' => 8,  'operator' => 'in', 'value' => [
                                            'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
                                        ]],
                                        ['question_id' => 11, 'operator' => 'in', 'value' => [
                                            'He sido víctima de violencia de género',
                                            'Estoy en riesgo de exclusión social',
                                            'Soy joven extutelado/a',
                                            'He estado en prisión (exconvicto/a)',
                                        ]],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
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

                        // 10) Usuarios que viven con convivientes y con supuestos especiales
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
                                            'question_id' => 8,
                                            'operator' => 'in',
                                            'value' => [
                                                'Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones',
                                            ],
                                        ],
                                        [
                                            'question_id' => 8,
                                            'operator' => 'in',
                                            'value' => [
                                                'Personas que vivan solas cuyos ingresos proceden de subsidios y no superen anualmente los 8.106,28€',
                                            ],
                                        ],
                                        [
                                            'question_id' => 8,
                                            'operator' => 'in',
                                            'value' => [
                                                'Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado/a por situación catastrófica',
                                            ],
                                        ],
                                    ],
                                ],
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
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
                    ],
                ]),
            ],

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
            'ayuda_id' => 8,
            'descripcion' => 'Requisitos PAV canarias',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
