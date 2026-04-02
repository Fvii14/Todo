<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonSeeder extends Seeder
{
    public function run(): void
    {
        $requisitos = [
            [
                'descripcion' => 'Debes ser menores de 36 años',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 40, 'operator' => 'less_than_years', 'value' => 36],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Debes contar con DNI o NIE (incluidos convivientes)',
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
                'descripcion' => 'Se requiere tener fuente regular de ingresos',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 46, 'operator' => '!=', 'value' => 0],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No debes tener propiedades (salvo excepciones justificadas)',
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
            [
                'descripcion' => 'Ni tú ni tus convivientes podéis tener deudas con Hacienda o la Seguridad Social',
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
                                ['question_id' => 44, 'operator' => '==', 'value' => 0],
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
                'descripcion' => 'El contrato de alquiler debe durar al menos 12 meses',
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
                'descripcion' => 'Debes estar empadronado en la vivienda alquilada',
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
                'descripcion' => 'No debes tener relación familiar hasta 2º de consaguinidad ni vínculo empresarial con el arrendador',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 16, 'operator' => '==', 'value' => 0], // 0 = No tiene parentesco
                    ],
                ]),
            ],
            [
                'descripcion' => 'Los pagos del alquiler deben hacerse por transferencia, ingreso en cuenta o Bizum',
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
                'descripcion' => 'Los convivientes deben ser menores de 36 años y no tener relación familiar (hasta 2º de consanguinidad) ni vínculo empresarial con el arrendador',
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
                                        'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato',
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
                'descripcion' => 'La unidad de convivencia supera el límite de ingresos permitido',
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
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
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
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                                ['question_id' => 29, 'operator' => '==', 'value' => 0],
                                ['question_id' => 30, 'operator' => '==', 'value' => 0],
                                [
                                    'condition' => 'OR',
                                    'rules' => [
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 5, 'operator' => '==', 'value' => 2],
                                                ['question_id' => 28, 'operator' => '<=', 'value' => 33600],
                                            ],
                                        ],
                                        [
                                            'condition' => 'AND',
                                            'rules' => [
                                                ['question_id' => 5, 'operator' => '>', 'value' => 2],
                                                ['question_id' => 28, 'operator' => '<=', 'value' => 42000],
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
                'descripcion' => 'Precio del alquiler supera el límite permitido según municipio',
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

        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 6, // ID de la ayuda BAJ COMUNIDAD VALENCIANA
            'descripcion' => 'Requisitos BAJ C.Valenciana',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Requisitos para IMV
        $requisitos = [
            [
                'descripcion' => 'Debes contar con DNI o NIE',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'tiene_dni')->first()->id,
                            'operator' => '==',
                            'value' => 1,
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Todos los convivientes menores de 14 años deben tener DNI o NIE',
                'json_regla' => json_encode([
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'menores_14_convivencia_tienen_dni')->first()->id,
                            'operator' => '==',
                            'value' => 1,
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'No te encuentras en situaciones requeridas (protección de menores, orfandad, salida de prisión…).',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'situaciones_imv')->first()->id,
                            'operator' => 'not_in',
                            'value' => [
                                'Ninguna de las anteriores',
                            ],
                        ],
                    ],
                    'condition' => 'AND',
                ]),
            ],
            [
                'descripcion' => 'Si estás cobrando ya el IMV no puedes solicitarlo.',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'cual_fuente_de_ingresos')->first()->id,
                            'operator' => 'not_in',
                            'value' => [
                                'Ingreso mínimo vital',
                            ],
                        ],
                    ],
                    'condition' => 'AND',
                ]),
            ],
        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 2,
            'descripcion' => 'Requisitos para el IMV en 2025',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Requisitos para Ayuda 100 por hijo
        $requisitos = [
            [
                'descripcion' => 'Debes tener un hijo menor de 3 años (o adoptado)',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'otro_hijo_menor3')->first()->id,
                            'operator' => 'not_in',
                            'value' => [
                                'No',
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Debes tener un hijo o tenerlo pronto',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'tiene_hijos_o_pronto')->first()->id,
                            'operator' => '==',
                            'value' => 1,
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Debes estar cobrando una prestación, subsidio o al menos haber cotizado 30 días desde el nacimiento de tu hijo/a',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'cotizado_30_dias_nacimiento_hijo')->first()->id,
                            'operator' => '==',
                            'value' => 1,
                        ],
                        [
                            'question_id' => Question::where('slug', 'tienePrestaciones')->first()->id,
                            'operator' => '==',
                            'value' => 1,
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Debes encontrar en alguna de las situaciones para esta ayuda',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'situaciones_100_por_hijo')->first()->id,
                            'operator' => 'not_in',
                            'value' => [
                                'Ninguna de las anteriores',
                            ],
                        ],
                    ],
                ]),
            ],
        ];

        DB::table('ayuda_requisitos_json')->insert([
            'ayuda_id' => 1,
            'descripcion' => 'Requisitos para la ayuda de 100€ por hijo',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
