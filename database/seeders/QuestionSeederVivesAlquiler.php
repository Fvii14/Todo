<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeederVivesAlquiler extends Seeder
{
    public function run()
    {
        DB::table('questions')->insert([
            [
                'slug' => 'vives_alquiler',
                'text' => '¿Actualmente estás viviendo de alquiler?',
                'sub_text' => 'Responde “Sí” si ya estás residiendo en una vivienda alquilada con o sin contrato formal.',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,

            ],
            [
                'slug' => 'quieres_vives_alquiler',
                'text' => '¿Tienes pensado irte a vivir de alquiler este año?',
                'sub_text' => 'Esta pregunta es clave: algunas ayudas se pueden conceder incluso si aún no vives de alquiler, siempre que tengas intención de hacerlo pronto. 💡 Hay convocatorias que aprueban la ayuda sin contrato, y te dan un plazo para alquilar y empezar a cobrar la subvención.',
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
