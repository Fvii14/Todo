<?php

namespace Database\Seeders;

use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaRequisitoSeeder extends Seeder
{
    public function run()
    {
        DB::table('ayuda_requisitos')->insert([
            'ayuda_id' => 1,
            'question_id' => Question::where('slug', 'tiene_hijos_o_pronto')->first()->id,
            'tipo_comparacion' => 'igual',
            'valor1' => '1', // espera valor exacto 1
            'valor2' => null,
            'obligatorio' => true,
            'condicion_previa_id' => null,
            'condicion_valor_esperado' => null,
            'excepcion' => null,
            'observaciones' => 'Debe responder 0 (No) para cumplir este requisito.',
            'respuesta_expected' => '0',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
