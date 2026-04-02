<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnyadirPreguntasCCAASeeder extends Seeder
{
    public function run(): void
    {
        DB::table('questions')->insert([
            [
                // ESTA PREGUNTA ES SOLO PARA CATALUÑA, CAMBIAMOS LA 18 POR ESTA
                'slug' => 'catalunya-recibo-alquiler',
                'text' => '¿Los recibos del alquiler los pagas por transferencia bancaria, Bizum, ingreso o a través del administrador de la propiedad?',
                'sub_text' => 'Al administrador de la propiedad también se le conoce como el administrador de fincas.',
                'type' => 'boolean',
                'options' => null,
                'sector' => 'alquiler',
                'categoria' => 'vivienda',
                'regex_id' => null,
                'exclude_none_option' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // LAS SIGUIENTES 3 PREGUNTAS SON PARA EL PAV DE MADRID
            [
                'slug' => 'madrid-convivientes-menores-36',
                'text' => '¿TODOS los convivientes sois menores de 36 años?',
                'sub_text' => 'Incluyéndote a ti.',
                'type' => 'boolean',
                'options' => null,
                'sector' => 'alquiler',
                'categoria' => 'vivienda',
                'regex_id' => null,
                'exclude_none_option' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'slug' => 'madrid-convivientes-mayores-65',
                'text' => '¿TODOS los convivientes sois mayores de 65 años?',
                'sub_text' => 'Incluyéndote a ti.',
                'type' => 'boolean',
                'options' => null,
                'sector' => 'alquiler',
                'categoria' => 'vivienda',
                'regex_id' => null,
                'exclude_none_option' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'slug' => 'madrid-convivientes-entre-36-65',
                'text' => '¿Todos los convivientes tenéis entre 36 y 65 años?',
                'sub_text' => 'Incluyéndote a ti.',
                'type' => 'boolean',
                'options' => null,
                'sector' => 'alquiler',
                'categoria' => 'vivienda',
                'regex_id' => null,
                'exclude_none_option' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // ESTA ES PARA EL BAJ DE PAÍS VASCO
            [
                'slug' => 'pais-vasco-rgi',
                'text' => ' ¿Estás recibiendo la Renta de Garantía de Ingresos (RGI) o alguna otra ayuda económica relacionada con la administración pública?',
                'sub_text' => 'Por ejemplo: ayudas económicas para personas en situación vulnerable, sin ingresos o en riesgo de exclusión social.',
                'type' => 'boolean',
                'options' => null,
                'sector' => 'alquiler',
                'categoria' => 'vivienda',
                'regex_id' => null,
                'exclude_none_option' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // ESTAS DOS SON PARA EL PAV DE ANDALUCÍA
            [
                'slug' => 'andalucia-grupo-vulnerable',
                'text' => ' ¿Perteneces tú o en su caso algún miembro de la Unidad de Convivencia a alguno de estos grupos considerados vulnerables?',
                'sub_text' => 'Es importantísimo que leas TODAS las opciones de la siguiente pregunta, porque puede decidir que te den la ayuda.',
                'type' => 'select',
                'options' => json_encode(['Ninguna de las anteriores', 'Familia numerosa, monoparental, persona con discapacidad ±33%', "Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto\/a", 'Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones', 'Personas que vivan solas cuyos ingresos proceden de subsidios y no superen anualmente los 8.106,28€', "Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado\/a por situaciónn catastrófica", 'Víctimas de trata con fines de explotación sexual, de violencia sexual o personas sin hogar']),
                'sector' => 'alquiler',
                'categoria' => 'vivienda',
                'regex_id' => null,
                'exclude_none_option' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'slug' => 'andalucia-grupo-vulnerable2',
                'text' => ' Has marcado que tú o algún miembro de tu Unidad de Convivencia es víctima de trata con fines de explotación sexual, de violencia sexual o persona sin hogar. Por favor, especifica cuál de ellas es la situación concreta.',
                'sub_text' => 'Es importantísimo que nos des esta información para sabar si te pertenece esta ayuda.',
                'type' => 'select',
                'options' => json_encode(['Víctimas de trata con fines de explotación sexual', 'Víctimas de violencia sexual', 'Personas sin hogar']),
                'sector' => 'alquiler',
                'categoria' => 'vivienda',
                'regex_id' => null,
                'exclude_none_option' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);
    }
}
