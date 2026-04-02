<?php

namespace Database\Seeders;

use App\Enums\QuestionnaireTipo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuestionariosConvivientesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $formularios = [
            ['name' => 'BAJ Cataluña - Conviviente', 'ayuda_id' => 4, 'slug' => 'baj-cataluna-conviviente'],
            ['name' => 'BAJ Comunidad Valenciana - Conviviente', 'ayuda_id' => 6, 'slug' => 'baj-cv-conviviente'],
            ['name' => 'BAJ Canarias - Conviviente', 'ayuda_id' => 9, 'slug' => 'baj-canarias-conviviente'],
            ['name' => 'BAJ La Rioja - Conviviente', 'ayuda_id' => 11, 'slug' => 'baj-la-rioja-conviviente'],
            ['name' => 'BAJ Extremadura - Conviviente', 'ayuda_id' => 13, 'slug' => 'baj-extremadura-conviviente'],
            ['name' => 'BAJ Asturias - Conviviente', 'ayuda_id' => 15, 'slug' => 'baj-asturias-conviviente'],
            ['name' => 'BAJ Aragón - Conviviente', 'ayuda_id' => 17, 'slug' => 'baj-aragon-conviviente'],
            ['name' => 'BAJ Castilla y León - Conviviente', 'ayuda_id' => 19, 'slug' => 'baj-cyl-conviviente'],
            ['name' => 'BAJ Castilla-La Mancha - Conviviente', 'ayuda_id' => 21, 'slug' => 'baj-clm-conviviente'],
            ['name' => 'BAJ Baleares - Conviviente', 'ayuda_id' => 23, 'slug' => 'baj-baleares-conviviente'],
            ['name' => 'BAJ Andalucía - Conviviente', 'ayuda_id' => 25, 'slug' => 'baj-andalucia-conviviente'],
            ['name' => 'BAJ Murcia - Conviviente', 'ayuda_id' => 27, 'slug' => 'baj-murcia-conviviente'],
            ['name' => 'BAJ Madrid - Conviviente', 'ayuda_id' => 29, 'slug' => 'baj-madrid-conviviente'],
            ['name' => 'BAJ Cantabria - Conviviente', 'ayuda_id' => 31, 'slug' => 'baj-cantabria-conviviente'],
            ['name' => 'BAJ Galicia - Conviviente', 'ayuda_id' => 33, 'slug' => 'baj-galicia-conviviente'],
            ['name' => 'BAJ País Vasco - Conviviente', 'ayuda_id' => 35, 'slug' => 'baj-euskadi-conviviente'],
            ['name' => 'BAJ Ceuta - Conviviente', 'ayuda_id' => 37, 'slug' => 'baj-ceuta-conviviente'],
            ['name' => 'BAJ Melilla - Conviviente', 'ayuda_id' => 39, 'slug' => 'baj-melilla-conviviente'],
            ['name' => 'BAJ Navarra - Conviviente', 'ayuda_id' => 41, 'slug' => 'baj-navarra-conviviente'],

            ['name' => 'PAV Cataluña - Conviviente', 'ayuda_id' => 5, 'slug' => 'pav-cataluna-conviviente'],
            ['name' => 'PAV Comunidad Valenciana - Conviviente', 'ayuda_id' => 7, 'slug' => 'pav-cv-conviviente'],
            ['name' => 'PAV Canarias - Conviviente', 'ayuda_id' => 8, 'slug' => 'pav-canarias-conviviente'],
            ['name' => 'PAV La Rioja - Conviviente', 'ayuda_id' => 10, 'slug' => 'pav-la-rioja-conviviente'],
            ['name' => 'PAV Extremadura - Conviviente', 'ayuda_id' => 12, 'slug' => 'pav-extremadura-conviviente'],
            ['name' => 'PAV Asturias - Conviviente', 'ayuda_id' => 14, 'slug' => 'pav-asturias-conviviente'],
            ['name' => 'PAV Aragón - Conviviente', 'ayuda_id' => 16, 'slug' => 'pav-aragon-conviviente'],
            ['name' => 'PAV Castilla y León - Conviviente', 'ayuda_id' => 18, 'slug' => 'pav-cyl-conviviente'],
            ['name' => 'PAV Castilla-La Mancha - Conviviente', 'ayuda_id' => 20, 'slug' => 'pav-clm-conviviente'],
            ['name' => 'PAV Baleares - Conviviente', 'ayuda_id' => 22, 'slug' => 'pav-baleares-conviviente'],
            ['name' => 'PAV Andalucía - Conviviente', 'ayuda_id' => 24, 'slug' => 'pav-andalucia-conviviente'],
            ['name' => 'PAV Murcia - Conviviente', 'ayuda_id' => 26, 'slug' => 'pav-murcia-conviviente'],
            ['name' => 'PAV Madrid - Conviviente', 'ayuda_id' => 28, 'slug' => 'pav-madrid-conviviente'],
            ['name' => 'PAV Cantabria - Conviviente', 'ayuda_id' => 30, 'slug' => 'pav-cantabria-conviviente'],
            ['name' => 'PAV Galicia - Conviviente', 'ayuda_id' => 32, 'slug' => 'pav-galicia-conviviente'],
        ];

        foreach ($formularios as &$form) {
            $form['active'] = 1;
            $form['tipo'] = QuestionnaireTipo::CONVIVIENTE->value;
            $form['created_at'] = $now;
            $form['updated_at'] = $now;
            $form['redirect_url'] = null;
        }

        DB::table('questionnaires')->insert($formularios);
    }
}
