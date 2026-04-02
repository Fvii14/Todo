<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonBAJCatalunyaSeeder extends Seeder
{
    public function run(): void
    {
        Question::insert([
            [
                'slug' => 'cual_fuente_de_ingresos',
                'text' => '¿Tienes una fuente regular de ingresos?',
                'sub_text' => '',
                'type' => 'select',
                'options' => json_encode(['Prestación por desempleo', 'Ingreso mínimo vital', 'Baja laboral', 'Pensión (jubilación, viudedad)', 'No percibo ningún ingreso']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
            ],
        ]);
        $requisitos = [
            [
                'descripcion' => 'Tener menos de 36 años',
                'json_regla' => json_encode([ // Añadido json_encode()
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 40, 'operator' => 'less_than_years', 'value' => 36],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Coexistencia permitida si todos los convivientes < 36 y sin parentesco',
                'json_regla' => json_encode([ // Añadido json_encode()
                    'condition' => 'OR',
                    'rules' => [
                        [
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
                'descripcion' => 'Tener DNI, NIE o Pasaporte',
                'json_regla' => json_encode([ // Añadido json_encode()
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
                                ['question_id' => 23, 'operator' => '==', 'value' => 1],
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
                                ['question_id' => 34, 'operator' => '!=', 'value' => ''],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Fuente regular de ingresos',
                'json_regla' => json_encode([ // Añadido json_encode()
                    'condition' => 'OR',
                    'rules' => [
                        ['question_id' => 46, 'operator' => '!=', 'value' => 0],
                        ['question_id' => Question::where('slug', 'cual_fuente_de_ingresos')->first()->id, 'operator' => '!=', 'value' => 'No percibo ningún ingreso'],
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
                'descripcion' => 'No tener deudas con Hacienda y Seguridad Social',
                'json_regla' => json_encode([ // Añadido json_encode()
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
                'json_regla' => json_encode([ // Añadido json_encode()
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
                'json_regla' => json_encode([ // Añadido json_encode()
                    'condition' => 'AND',
                    'rules' => [
                        ['question_id' => 16, 'operator' => '==', 'value' => 0],
                    ],
                ]),
            ],
            [
                'descripcion' => 'Límite de ingresos según unidad de convivencia (Madrid)', // Nota: La descripción menciona Madrid
                'json_regla' => json_encode([ // Añadido json_encode()
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
                                ['question_id' => 43, 'operator' => '<=', 'value' => 25200],
                            ],
                        ],
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
                'descripcion' => 'Precio alquiler no supera el límite',
                'json_regla' => json_encode([ // Añadido json_encode()
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 600,
                        'habitacion' => 300,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'Ámbito Metropolitano de Barcelona',
                            'municipios' => [
                                'Barcelona', "Hospitalet de Llobregat, L\'", 'Badalona', 'Cornellà de Llobregat',
                                'Sant Boi de Llobregat', 'Prat de Llobregat, El', 'Gavà', 'Castelldefels',
                                'Viladecans', 'Esplugues de Llobregat', 'Sant Adrià de Besòs',
                                'Santa Coloma de Gramenet', 'Mataró', 'Granollers', 'Sabadell', 'Terrassa',
                                'Rubí', 'Sant Cugat del Vallès', 'Cerdanyola del Vallès', 'Mollet del Vallès',
                                'Montcada i Reixac', 'Vilanova i la Geltrú', 'Sitges', 'Igualada',
                            ],
                            'piso_completo' => 900,
                            'habitacion' => 450,
                        ],
                        [
                            'nombre' => 'Resto demarcación Barcelona',
                            'municipios' => [
                                'Manresa', 'Vic', 'Berga', 'Moià', 'Súria', 'Gironella', 'Puig-reig',
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                        ],
                        [
                            'nombre' => 'Demarcación de Girona',
                            'municipios' => [
                                'Girona', 'Figueres', 'Olot', 'Blanes', 'Lloret de Mar', 'Banyoles', 'Roses',
                                'Palafrugell', 'Salt',
                            ],
                            'piso_completo' => 750,
                            'habitacion' => 400,
                        ],
                        [
                            'nombre' => 'Demarcación de Lleida',
                            'municipios' => [
                                'Lleida', 'Balaguer', 'Tàrrega', "Seu d\'Urgell, La", 'Mollerussa',
                                'Cervera', 'Solsona', 'Vielha e Mijaran',
                            ],
                            'piso_completo' => 600,
                            'habitacion' => 300,
                        ],
                        [
                            'nombre' => 'Demarcación de Tarragona',
                            'municipios' => [
                                'Tarragona', 'Reus', 'Valls', 'Cambrils', 'Salou', 'El Vendrell',
                                'Torredembarra', 'Calafell',
                            ],
                            'piso_completo' => 700,
                            'habitacion' => 350,
                        ],
                        [
                            'nombre' => "Les Terres de l'Ebre",
                            'municipios' => [
                                'Tortosa', 'Amposta', 'Deltebre', "Ràpita, La'", 'Ulldecona',
                                'Alcanar', "Móra d\'Ebre", 'Gandesa',
                            ],
                            'piso_completo' => 600,
                            'habitacion' => 300,
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
            'ayuda_id' => 4, // ID de la ayuda BAJ Cataluña
            'descripcion' => 'Requisitos BAJ Cataluña',
            'json_regla' => json_encode($requisitos), // Única codificación a JSON del array de requisitos aquí
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
