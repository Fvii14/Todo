<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSolicitanteSeeder extends Seeder
{
    public function run()
    {
        DB::table('questions')->insert([
            [
                'slug' => 'pertenece-grupo-vulnerable-solicitante',
                'text' => '¿Perteneces a algún grupo vulnerable?',
                'sub_text' => 'Selecciona "Sí" solo si formas parte de un grupo vulnerable reconocido (por ejemplo: víctimas de violencia de género, personas sin hogar, con discapacidad, etc.).',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'grupo_vulnerable_solicitante',
                'text' => '¿A qué grupo vulnerable perteneces?',
                'sub_text' => 'Elige entre las opciones disponibles el grupo que mejor describa tu situación (por ejemplo: discapacidad, monoparental, exclusión social...).',
                'type' => 'select',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ],
            [
                'slug' => 'ha_hecho_renta_conviviente',
                'text' => '¿El conviviente presentó la declaración de la renta el año pasado?',
                'sub_text' => 'Marca “Sí” si la persona con la que convives presentó la declaración de la renta correspondiente al último ejercicio fiscal completo.',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ],
        ]);
    }
}
