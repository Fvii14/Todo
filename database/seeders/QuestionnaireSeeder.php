<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireSeeder extends Seeder
{
    public function run()
    {
        DB::table('questionnaires')->insert([
            [
                'name' => 'Formulario Collector',
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tipo' => 'collector',
                'slug' => 'form_collector',
            ],
            [
                'name' => 'Ayuda 100€ por hijo',
                'active' => 1,
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tipo' => 'pre',
                'slug' => 'ayuda_100_por_hijo',
            ],
        ]);
        $data = [
            ['name' => 'Bono Alquiler Joven Cataluña'],
            ['name' => 'Bono Alquiler Joven Comunidad Valenciana'],
            ['name' => 'Bono Alquiler Joven Canarias'],
            ['name' => 'Bono Alquiler Joven La Rioja'],
            ['name' => 'Bono Alquiler Joven Extremadura'],
            ['name' => 'Bono Alquiler Joven Asturias'],
            ['name' => 'Bono Alquiler Joven Aragón'],
            ['name' => 'Bono Alquiler Joven Castilla y León'],
            ['name' => 'Bono Alquiler Joven Castilla-La Mancha'],
            ['name' => 'Bono Alquiler Joven Baleares'],
            ['name' => 'Bono Alquiler Joven Andalucía'],
            ['name' => 'Bono Alquiler Joven Murcia'],
            ['name' => 'Bono Alquiler Joven Madrid'],
            ['name' => 'Bono Alquiler Joven Cantabria'],
            ['name' => 'Bono Alquiler Joven Galicia'],
            ['name' => 'Bono Alquiler Joven País Vasco'],
            ['name' => 'Bono Alquiler Joven Ceuta'],
            ['name' => 'Bono Alquiler Joven Melilla'],
            ['name' => 'Bono Alquiler Joven Navarra'],
        ];

        foreach ($data as $item) {
            DB::table('questionnaires')->insert([
                'name' => $item['name'],
                'redirect_url' => null,
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tipo' => 'pre',
            ]);
        }

        DB::table('questionnaires')->insert([
            [
                'name' => 'Programa Estatal de Vivienda Cataluña -36',
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tipo' => 'pre',
                'redirect_url' => null,
            ],
        ]);

        $data = [
            ['Comunidad Valenciana', 6],
            ['Canarias', 7],
            ['La Rioja', 9],
            ['Extremadura', 11],
            ['Asturias', 13],
            ['Aragón', 15],
            ['Castilla y León', 17],
            ['Castilla-La Mancha', 19],
            ['Baleares', 21],
            ['Andalucía', 23],
            ['Murcia', 25],
            ['Madrid', 27],
            ['Cantabria', 29],
            ['Galicia', 31],
            ['País Vasco', 33],
            ['Ceuta', 35],
            ['Melilla', 37],
            ['Navarra', 39],
            ['General', 2],
        ];

        $insertData = [];

        foreach ($data as $item) {
            $insertData[] = [
                'name' => 'Programa Estatal de Vivienda '.$item[0],
                'redirect_url' => null,
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tipo' => 'pre',
            ];
        }

        DB::table('questionnaires')->insert($insertData);

        DB::table('questionnaires')->insert([
            [
                'name' => 'Ingreso Mínimo Vital (I.M.V)',
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
