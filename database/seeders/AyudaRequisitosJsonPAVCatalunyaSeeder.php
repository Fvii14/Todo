<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitosJsonPAVCatalunyaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requisitos = [

            // !!------------Hay que tener contrato-----------------------------------------------------------------------------------------
            // ___GENERAL___

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

                'descripcion' => 'Tener menos de 36 años',
                'json_regla' => json_encode([
                    'condition' => 'AND',
                    'rules' => [
                        [
                            'question_id' => Question::where('slug', 'fecha_nacimiento')->first()->id,
                            'operator' => 'less_than_years',
                            'value' => 36,
                        ],
                    ],
                ]),

            ],

            // !!---Contrato duración más de 12 meses pero no para vivienda------------------------------------------------------------------------------------------------------------------------------
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
            // !!---NO tener propiedades --------------------------------------------------------------------------------------------------------------
            // ____GENERAL____
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

            // !!---Para TODO tipo de alquiler, el solicitante y cualquiera de los convivientes tiene que tener unos ingresos---------------------------------------------
            // __GENERAL___
            // FALTA AÑADIR LA PREGUNTA QUE COMPRUEBA SI TODOS LOS CONVIVIENTES TIENE FUENTE DE INGRESOS EN EL CASO DE NO TENERLOS EL USUARIO!!!!
            [
                'descripcion' => 'Límite de ingresos según unidad de convivencia',
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
            // !!!-----------------Pagar como máximo los siguientes importes por su alquiler, iguales que en la tabla del-----------------------------------------------------------------------------------

            // ___GENERAL___
            [
                'descripcion' => 'Precio del alquiler supera el límite permitido',
                'json_regla' => json_encode([
                    'tipo' => 'precio_alquiler_limite',
                    'default' => [
                        'piso_completo' => 600,
                        'habitacion' => 300,
                    ],
                    'grupos' => [
                        [
                            'nombre' => 'Ámbito Metropolitano de Barcelona',
                            'municipios' => [
                                'Barcelona', "Hospitalet de Llobregat, L'", 'Badalona', 'Santa Coloma de Gramenet', 'Sant Adrià de Besòs',
                            ],
                            'piso_completo' => 950,
                            'habitacion' => 450,
                            'familia_numerosa_general' => 1100,
                            'familia_numerosa_especial' => 1100,
                        ],
                        [
                            'nombre' => 'Baix Llobregat',
                            'municipios' => [
                                'Castelldefels', 'Cornellà de Llobregat', 'Prat de Llobregat, El', 'Esplugues de Llobregat', 'Gavà', 'Molins de Rei', 'Pallejà', 'Sant Boi de Llobregat', 'Sant Feliu de Llobregat', 'Sant Joan Despí', 'Sant Just Desvern', 'Santa Coloma de Cervelló', 'Viladecans', 'Abrera', 'Begues', 'Castellví de Rosanes', 'Cervelló', 'Collbató', 'Corbera de Llobregat', 'Esparreguera', 'Martorell', 'Olesa de Montserrat', 'Palma de Cervelló, La', 'Papiol, El', 'Sant Andreu de la Barca', 'Sant Climent de Llobregat', 'Sant Esteve Sesrovires', 'Sant Vicenç dels Horts', 'Torrelles de Llobregat', 'Vallirana',
                            ],
                            'piso_completo' => 950,
                            'habitacion' => 450,
                            'familia_numerosa_general' => 1100,
                            'familia_numerosa_especial' => 1100,
                        ],
                        [
                            'nombre' => 'Garraf',
                            'municipios' => [
                                'Vilanova i la Geltrú', 'Sitges', 'Sant Pere de Ribes', 'Canyelles', 'Cubelles', 'Olivella',
                            ],
                            'piso_completo' => 950,
                            'habitacion' => 450,
                            'familia_numerosa_general' => 1100,
                            'familia_numerosa_especial' => 1100,
                        ],
                        [
                            'nombre' => 'Maresme',
                            'municipios' => [
                                'Alella', 'Arenys de Mar', 'Arenys de Munt', 'Argentona', "Caldes d'Estrac", 'Calella', 'Canet de Mar', 'Cabrera de Mar', 'Dosrius', 'Masnou, El', 'Malgrat de Mar', 'Mataró', 'Montgat', 'Òrrius', 'Palafolls', 'Pineda de Mar', 'Premià de Dalt', 'Premià de Mar', 'Sant Andreu de Llavaneres', 'Sant Cebrià de Vallalta', 'Sant Iscle de Vallalta', 'Sant Pol de Mar', 'Sant Vicenç de Montalt', 'Santa Susanna', 'Teià', 'Tiana', 'Tordera', 'Vilassar de Dalt', 'Vilassar de Mar',
                            ],
                            'piso_completo' => 950,
                            'habitacion' => 450,
                            'familia_numerosa_general' => 1100,
                            'familia_numerosa_especial' => 1100,
                        ],
                        [
                            'nombre' => 'Vallès Occidental',
                            'municipios' => [
                                'Badia del Vallès', 'Barberà del Vallès', 'Castellar del Vallès', 'Castellbisbal', 'Cerdanyola del Vallès', 'Gallifa', 'Matadepera', 'Montcada i Reixac', 'Palau-solità i Plegamans', 'Polinyà', 'Rellinars', 'Ripollet', 'Rubí', 'Sabadell', 'Sant Cugat del Vallès', 'Sant Llorenç Savall', 'Sant Quirze del Vallès', 'Santa Perpètua de Mogoda', 'Sentmenat', 'Terrassa', 'Ullastrell', 'Vacarisses',
                            ],
                            'piso_completo' => 950,
                            'habitacion' => 450,
                            'familia_numerosa_general' => 1100,
                            'familia_numerosa_especial' => 1100,
                        ],
                        [
                            'nombre' => 'Vallès Oriental',
                            'municipios' => [
                                'Aiguafreda', "Ametlla del Vallès, L'", 'Bigues i Riells', 'Caldes de Montbui', 'Campins', 'Canovelles', 'Cardedeu', 'Cànoves i Samalús', 'Figaró-Montmany', 'Fogars de Montclús', 'Franqueses del Vallès, Les', 'Garriga, La', 'Gualba', 'Granollers', 'Llagosta, La', 'Lliçà de Vall', "Lliçà d'Amunt", 'Llinars del Vallès', 'Martorelles', 'Mollet del Vallès', 'Montmeló', 'Montornès del Vallès', 'Parets del Vallès', 'Pinós', 'Roca del Vallès, La', 'Sant Antoni de Vilamajor', 'Sant Celoni', 'Sant Fost de Campsentelles', 'Sant Pere de Vilamajor', 'Santa Eulàlia de Ronçana', 'Santa Maria de Martorelles', 'Santa Maria de Palautordera', 'Tagamanent', 'Vallgorguina', 'Vallromanes', 'Vilanova del Vallès',
                            ],
                            'piso_completo' => 950,
                            'habitacion' => 450,
                            'familia_numerosa_general' => 1100,
                            'familia_numerosa_especial' => 1100,
                        ],
                        [
                            'nombre' => 'Alt Penedès',
                            'municipios' => [
                                'Avinyonet del Penedès', 'Cabanyes, Les', 'Castellet i la Gornal', 'Castellví de la Marca', 'Font-rubí', 'Gelida', 'Granada, La', 'Mediona', 'Olèrdola', 'Olesa de Bonesvalls', 'Pacs del Penedès', 'Pla del Penedès, El', 'Pontons', 'Puigdàlber', 'Sant Cugat Sesgarrigues', "Sant Llorenç d'Hortons", 'Sant Martí Sarroca', 'Sant Pere de Riudebitlles', 'Sant Quintí de Mediona', "Sant Sadurní d'Anoia", 'Santa Margarida i els Monjos', 'Santa Fe del Penedès', 'Subirats', 'Torrelavit', 'Torrelles de Foix', 'Vilafranca del Penedès', 'Vilobí del Penedès',
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Anoia',
                            'municipios' => [
                                'Argençola', 'Bellprat', 'Bruc, El', "Cabrera d'Anoia", 'Calaf', 'Calonge de Segarra', 'Capellades', 'Carme', 'Castellfollit de Riubregós', 'Castellolí', 'Copons', 'Hostalets de Pierola, Els', 'Igualada', 'Jorba', 'Llacuna, La', 'Masquefa', 'Montmaneu', 'Òdena', 'Orpí', 'Piera', 'Pobla de Claramunt, La', 'Prats de Rei, Els', 'Pujalt', 'Rubió', 'Santa Margarida de Montbui', 'Santa Maria de Miralles', 'Sant Martí de Tous', 'Sant Martí Sesgueioles', 'Sant Pere Sallavinera', 'Veciana', 'Vilanova del Camí', "Vallbona d'Anoia", 'La Torre de Claramunt',
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Bages',
                            'municipios' => [
                                'Aguilar de Segarra', 'Artés', 'Avinyó', 'Balsareny', 'Callús', 'Cardona', 'Castellbell i el Vilar', 'Castellfollit del Boix', 'Castellgalí', 'Castellnou de Bages', 'Fonollosa', 'Gaià', 'Manresa', 'Marganell', 'Monistrol de Montserrat', 'Mura', 'Navarcles', 'Navàs', 'Pont de Vilomara i Rocafort, El', 'Rajadell', 'Sallent', 'Sant Feliu Sasserra', 'Sant Fruitós de Bages', 'Sant Joan de Vilatorrada', 'Sant Mateu de Bages', 'Sant Salvador de Guardiola', 'Sant Vicenç de Castellet', 'Santpedor', 'Súria',
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Berguedà',
                            'municipios' => [
                                'Avià', 'Bagà', 'Berga', 'Borredà', 'Capolat', 'Casserres', "Castell de l'Areny", "Castellar de n'Hug", 'Castellar del Riu', 'Cercs', "Espunyola, L'", 'Fígols', 'Gironella', 'Gisclareny', 'Gósol', 'Guardiola de Berguedà', 'Montclar', 'Montmajor', 'Nou de Berguedà, La', 'Olvan', 'Pobla de Lillet, La', 'Puig-reig', 'Quar, La', 'Sagàs', 'Saldes', 'Sant Jaume de Frontanyà', 'Santa Maria de Merlès', 'Vallcebre', 'Vilada', 'Viver i Serrateix', 'Sant Julià de Cerdanyola',
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Garraf',
                            'municipios' => [
                                'Vilanova i la Geltrú', 'Sitges', 'Sant Pere de Ribes', 'Canyelles', 'Cubelles', 'Olivella',
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Mojanès',
                            'municipios' => [
                                'Calders', 'Castellcir', 'Castellterçol', 'Collsuspina', "Estany, L'", 'Granera', 'Moià', 'Monistrol de Calders', 'Sant Quirze Safaja', "Santa Maria d'Oló",
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Osona',
                            'municipios' => [
                                'Alpens', 'Balenyà', 'Brull, El', 'Calldetenes', 'Centelles', 'Espinelves', 'Folgueroles', 'Gurb', "Esquirol, L'", 'Malla', 'Manlleu', 'Masies de Roda, Les', 'Masies de Voltregà, Les', 'Montesquiu', 'Muntanyola', 'Orís', 'Roda de Ter', 'Rupit i Pruit', 'Sant Agustí de Lluçanès', 'Sant Bartomeu del Grau', 'Sant Boi de Lluçanès', 'Sant Hipòlit de Voltregà', 'Sant Julià de Vilatorta', "Sant Martí d'Albars", 'Sant Martí de Centelles', 'Sant Pere de Torelló', 'Sant Vicenç de Torelló', 'Santa Cecília de Voltregà', 'Santa Eugènia de Berga', 'Santa Eulàlia de Riuprimer', 'Santa Maria de Besora', 'Seva', 'Sobremunt', 'Sora', 'Taradell', 'Tavertet', 'Tona', 'Torelló', 'Vic', 'Vidrà', 'Viladrau', 'Vilanova de Sau',
                            ],
                            'piso_completo' => 650,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Girona',
                            'municipios' => [
                                'Girona',
                            ],
                            'piso_completo' => 750,
                            'habitacion' => 400,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Tarragona',
                            'municipios' => [
                                'Aiguamúrcia', 'Alcover', 'Alió', 'Bràfim', 'Cabra del Camp', 'Figuerola del Camp', 'Montferri', 'Mont-ral', 'Nulles', 'El Pla de Santa Maria', "El Pont d'Armentera", 'Puigpelat', 'Querol', 'La Riba', 'Rodonyà', 'Santes Creus', 'Vallmoll', 'Valls', 'Vilabella', 'Vila-rodona', "Albiol, L'", "Aleixar, L'", 'Almoster', 'Alforja', "Almadrava, L'", 'Arbolí', "Argentera, L'", 'Cambrils', 'Capafonts', 'Castellvell del Camp', 'Colldejou', 'Les Borges del Camp', 'Montbrió del Camp', 'Mont-roig del Camp', 'Prades', 'Reus', 'Riudecanyes', 'Riudecols', 'Riudoms', 'Salou', "Vilanova d'Escornalbou", 'Vilaplana', 'Vinyols i els Arcs', 'Albinyana', "Arboç, L'", 'Banyeres del Penedès', 'Bellvei', 'Bisbal del Penedès, La', 'Bonastre', 'Calafell', 'Cunit', 'Llorenç del Penedès', 'Masllorenç', 'Masarbonès', 'Sant Jaume dels Domenys', 'Santa Oliva', 'Vendrell, El', 'Barberà de la Conca', 'Blancafort', 'Conesa', "Espluga de Francolí, L'", 'Forès', 'Montblanc', 'Passanant i Belltall', 'Pira', 'Pontils', 'Rocafort de Queralt', 'Santa Coloma de Queralt', 'Savallà del Comtat', 'Sarral', 'Senan', 'Solivella', 'Vallclara', 'Vilanova de Prades', 'Vilaverd', 'Vimbodí i Poblet', 'Bellmunt del Priorat', 'Bisbal de Falset, La', 'Cabacés', 'Capçanes', 'Cornudella de Montsant', 'Falset', 'Figuera, La', 'Gratallops', 'Lloar, El', 'Marçà', 'Margalef', 'Masroig, El', 'Molar, El', 'Morera de Montsant, La', 'Poboleda', 'Porrera', 'Pradell de la Teixeta', 'Torre de Fontaubella, La', 'Tarragona', 'Torroja del Priorat', 'Ulldemolins', 'Vilella Alta, La', 'Vilella Baixa, La',
                            ],
                            'piso_completo' => 700,
                            'habitacion' => 350,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Terres de L`Ebre y Lleida Baix Ebre',
                            'municipios' => [
                                'Aldover', 'Alfara de Carles', "Aldea, L'", "Ametlla de Mar, L'", "Ampolla, L'", 'Benifallet', 'Camarles', 'Deltebre', 'Paüls', 'Perelló, El', 'Roquetes', 'Tivenys', 'Tortosa', 'Xerta',
                            ],
                            'piso_completo' => 600,
                            'habitacion' => 300,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Montsià',
                            'municipios' => [
                                'Alcanar', 'Freginals', 'Galera, La', 'Godall', 'Mas de Barberans', 'Masdenverge', 'Santa Bàrbara', 'Sant Carles de la Ràpita', "Sant Jaume d'Enveja", 'Sénia, La', 'Ulldecona', 'Amposta',
                            ],
                            'piso_completo' => 600,
                            'habitacion' => 300,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Ribera d`Ebre',
                            'municipios' => [
                                'Ascó', 'Benissanet', 'Garcia', 'Ginestar', "Móra d'Ebre", 'Móra la Nova', "Palma d'Ebre, La", "Torre de l'Espanyol, La", 'Rasquera', "Riba-roja d'Ebre", 'Tivissa', 'Vinebre',
                            ],
                            'piso_completo' => 600,
                            'habitacion' => 300,
                            'familia_numerosa_general' => 900,
                            'familia_numerosa_especial' => 900,
                        ],
                        [
                            'nombre' => 'Terra Alta',
                            'municipios' => [
                                'Arnes', 'Batea', 'Bot', 'Caseres', "Corbera d'Ebre", 'Fatarella, La', 'Gandesa', 'Horta de Sant Joan', 'Pinell de Brai, El', 'Pobla de Massaluca, La', 'Prat de Comte', 'Vilalba dels Arcs',
                            ],
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
                'descripcion' => 'Fuente regular de ingresos',
                'json_regla' => json_encode([
                    'condition' => 'OR',
                    'rules' => [
                        ['question_id' => Question::where('slug', 'esta_trabajando')->first()->id, 'operator' => '!=', 'value' => 0],
                        ['question_id' => Question::where('slug', 'cual_fuente_de_ingresos')->first()->id, 'operator' => '!=', 'value' => 'No percibo ningún ingreso'],
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
            'ayuda_id' => 5, // ID de la ayuda PAV Catalunya
            'descripcion' => 'Requisitos PAV Catalunya <36',
            'json_regla' => json_encode($requisitos),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
