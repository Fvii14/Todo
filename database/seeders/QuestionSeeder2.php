<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder2 extends Seeder
{
    public function run()
    {
        DB::table('questions')->insert([
            [
                'slug' => 'tipo_via',
                'text' => 'Tipo de vía',
                'sub_text' => 'Selecciona el tipo de vía donde resides (por ejemplo: calle, avenida, paseo…).',
                'type' => 'select',
                'options' => json_encode([
                    'Calle',
                    'Avenida',
                    'Plaza',
                    'Camino',
                    'Carretera',
                    'Paseo',
                    'Travesía',
                    'Ronda',
                    'Glorieta',
                    'Callejón',
                    'Autovía',
                    'Autopista',
                    'Pasaje',
                    'Polígono',
                    'Urbanización',
                    'Barrio',
                    'Sector',
                    'Paraje',
                    'Vía',
                    'Lugar',
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'nombre_via',
                'text' => 'Nombre de la vía',
                'sub_text' => 'Nombre de la calle, avenida, plaza, etc. donde se encuentra tu vivienda.',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 23,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'numero_domicilio',
                'text' => 'Número del domicilio',
                'sub_text' => 'Introduce solo el número del portal o edificio, sin letras ni piso (ej. 12).',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 24,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'bloque',
                'text' => 'Bloque',
                'sub_text' => 'Introduce el número o letra del bloque si aplica. Déjalo vacío si tu edificio no tiene bloques diferenciados.',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 25,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'portal',
                'text' => 'Portal',
                'sub_text' => 'Por ejemplo: A, B, C... o déjalo vacío si no hay portal indicado.',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 25,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'escalera',
                'text' => 'Escalera',
                'sub_text' => 'Indica la letra o número de la escalera si existe. Si no, déjalo vacío.',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 25,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'piso',
                'text' => 'Piso',
                'sub_text' => 'Por ejemplo: 1º, 2º, 3º... sin letra ni portal. Déjalo vacío si no aplica.',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 26,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'puerta',
                'text' => 'Puerta',
                'sub_text' => 'Por ejemplo: A, B, Dcha (derecha), Izq (izquierda)... Déjalo vacío si no aplica.',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 27,
                'exclude_none_option' => false,
            ],
        ]);
    }
}
