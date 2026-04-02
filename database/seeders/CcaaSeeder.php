<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CcaaSeeder extends Seeder
{
    public function run()
    {
        $ccaas = [
            'Andalucía',
            'Cataluña',
            'Madrid',
            'Comunidad Valenciana',
            'Castilla y León',
            'Galicia',
            'Castilla-La Mancha',
            'País Vasco',
            'Canarias',
            'Extremadura',
            'Aragón',
            'Murcia',
            'Baleares',
            'Cantabria',
            'Asturias',
            'Navarra',
            'La Rioja',
            'Ceuta',
            'Melilla',
            'No residente',
        ];

        foreach ($ccaas as $nombre) {
            DB::table('ccaa')->updateOrInsert(['nombre_ccaa' => $nombre]);
        }
    }
}
