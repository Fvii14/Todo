<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganosTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::create(2025, 3, 24, 9, 53, 38);

        $organismos = [
            ['nombre_organismo' => 'Gobierno de Andalucía', 'ambito' => 'Autonomica', 'id_ccaa' => 1, 'imagen' => 'junta-andalucia.png'],
            ['nombre_organismo' => 'Gobierno de Cataluña', 'ambito' => 'Autonomica', 'id_ccaa' => 2, 'imagen' => 'generalitat-catalunya.png'],
            ['nombre_organismo' => 'Gobierno de Madrid', 'ambito' => 'Autonomica', 'id_ccaa' => 3, 'imagen' => 'comunidad-madrid.png'],
            ['nombre_organismo' => 'Gobierno de la Comunidad Valenciana', 'ambito' => 'Autonomica', 'id_ccaa' => 4, 'imagen' => 'generalitat-valenciana.png'],
            ['nombre_organismo' => 'Gobierno de Castilla y León', 'ambito' => 'Autonomica', 'id_ccaa' => 5, 'imagen' => 'junta-castilla-y-leon.png'],
            ['nombre_organismo' => 'Gobierno de Galicia', 'ambito' => 'Autonomica', 'id_ccaa' => 6, 'imagen' => 'junta-galicia.png'],
            ['nombre_organismo' => 'Gobierno de Castilla-La Mancha', 'ambito' => 'Autonomica', 'id_ccaa' => 7, 'imagen' => 'gob-castilla-la-mancha.png'],
            ['nombre_organismo' => 'Gobierno del País Vasco', 'ambito' => 'Autonomica', 'id_ccaa' => 8, 'imagen' => 'gob-pais-vasco.png'],
            ['nombre_organismo' => 'Gobierno de Canarias', 'ambito' => 'Autonomica', 'id_ccaa' => 9, 'imagen' => 'gob-canarias.png'],
            ['nombre_organismo' => 'Gobierno de Extremadura', 'ambito' => 'Autonomica', 'id_ccaa' => 10, 'imagen' => 'junta-extremadura.png'],
            ['nombre_organismo' => 'Gobierno de Aragón', 'ambito' => 'Autonomica', 'id_ccaa' => 11, 'imagen' => 'gob-aragon.png'],
            ['nombre_organismo' => 'Gobierno de Murcia', 'ambito' => 'Autonomica', 'id_ccaa' => 12, 'imagen' => 'gob-murcia.png'],
            ['nombre_organismo' => 'Gobierno de Baleares', 'ambito' => 'Autonomica', 'id_ccaa' => 13, 'imagen' => 'gob-baleares.png'],
            ['nombre_organismo' => 'Gobierno de Cantabria', 'ambito' => 'Autonomica', 'id_ccaa' => 14, 'imagen' => 'gob-cantabria.png'],
            ['nombre_organismo' => 'Gobierno de Asturias', 'ambito' => 'Autonomica', 'id_ccaa' => 15, 'imagen' => 'gob-asturias.png'],
            ['nombre_organismo' => 'Gobierno de Navarra', 'ambito' => 'Autonomica', 'id_ccaa' => 16, 'imagen' => 'gob-navarra.png'],
            ['nombre_organismo' => 'Gobierno de La Rioja', 'ambito' => 'Autonomica', 'id_ccaa' => 17, 'imagen' => 'gob-la-rioja.png'],
            ['nombre_organismo' => 'Gobierno de Ceuta', 'ambito' => 'Autonomica', 'id_ccaa' => 18, 'imagen' => 'gob-ceuta.png'],
            ['nombre_organismo' => 'Gobierno de Melilla', 'ambito' => 'Autonomica', 'id_ccaa' => 19, 'imagen' => 'gob-melilla.png'],
            ['nombre_organismo' => 'Gobierno de España', 'ambito' => 'Nacional', 'id_ccaa' => null, 'imagen' => 'gob-españa.png'],
        ];

        foreach ($organismos as $organismo) {
            DB::table('organos')->insert([
                'nombre_organismo' => $organismo['nombre_organismo'],
                'ambito' => $organismo['ambito'],
                'id_ccaa' => $organismo['id_ccaa'],
                'imagen' => $organismo['imagen'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
