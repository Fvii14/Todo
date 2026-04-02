<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaPeriodosSeeder extends Seeder
{
    public function run(): void
    {
        // IDs que NO necesitan periodo
        $excluir = [1, 2, 42, 43];

        // Periodo común para ayudas de alquiler
        $fechaInicio = '2025-01-01';
        $fechaFin = '2025-12-31';

        DB::table('ayudas')
            ->where('sector', 'vivienda')
            ->whereNotIn('id', $excluir)
            ->update([
                'fecha_inicio_periodo' => $fechaInicio,
                'fecha_fin_periodo' => $fechaFin,
            ]);
    }
}
