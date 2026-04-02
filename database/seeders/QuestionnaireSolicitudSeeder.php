<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireSolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        $ayudas = DB::table('ayudas')
            ->get();

        foreach ($ayudas as $ayuda) {

            // Creamos un nuevo cuestionario de tipo 'solicitud'
            DB::table('questionnaires')->insert([
                'name' => 'Formulario de solicitud - '.$ayuda->nombre_ayuda,
                'tipo' => 'solicitud',
                'ayuda_id' => $ayuda->id,
                'slug' => 'solicitud_'.$ayuda->nombre_ayuda,
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        }
    }
}
