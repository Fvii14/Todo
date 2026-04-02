<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionCategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id' => 1, 'categoria' => 'vivienda'],
            ['id' => 2, 'categoria' => 'vivienda'],
            ['id' => 3, 'categoria' => 'vivienda'],
            ['id' => 4, 'categoria' => 'datos-personales'],
            ['id' => 5, 'categoria' => 'convivientes'],
            ['id' => 6, 'categoria' => 'convivientes'],
            ['id' => 7, 'categoria' => 'convivientes'],
            ['id' => 8, 'categoria' => 'grupo-vulnerable'],
            ['id' => 9, 'categoria' => 'convivientes'],
            ['id' => 10, 'categoria' => 'grupo-vulnerable'],
            ['id' => 11, 'categoria' => 'convivientes'],
            ['id' => 12, 'categoria' => 'vivienda'],
            ['id' => 13, 'categoria' => 'vivienda'],
            ['id' => 14, 'categoria' => 'convivientes'],
            ['id' => 15, 'categoria' => 'convivientes'],
            ['id' => 16, 'categoria' => 'vivienda'],
            ['id' => 17, 'categoria' => 'vivienda'],
            ['id' => 18, 'categoria' => 'vivienda'],
            ['id' => 19, 'categoria' => 'vivienda'],
            ['id' => 20, 'categoria' => 'convivientes'],
            ['id' => 21, 'categoria' => 'convivientes'],
            ['id' => 22, 'categoria' => 'convivientes'],
            ['id' => 23, 'categoria' => 'convivientes'],
            ['id' => 24, 'categoria' => 'convivientes'],
            ['id' => 25, 'categoria' => 'convivientes'],
            ['id' => 26, 'categoria' => 'vivienda'],
            ['id' => 27, 'categoria' => 'vivienda'],
            ['id' => 28, 'categoria' => 'convivientes'],
            ['id' => 29, 'categoria' => 'convivientes'],
            ['id' => 30, 'categoria' => 'convivientes'],
            ['id' => 31, 'categoria' => 'vivienda'],
            ['id' => 32, 'categoria' => 'vivienda'],
            ['id' => 33, 'categoria' => 'datos-personales'],
            ['id' => 34, 'categoria' => 'datos-personales'],
            ['id' => 35, 'categoria' => 'datos-personales'],
            ['id' => 36, 'categoria' => 'datos-personales'],
            ['id' => 37, 'categoria' => 'datos-personales'],
            ['id' => 38, 'categoria' => 'datos-personales'],
            ['id' => 39, 'categoria' => 'datos-personales'],
            ['id' => 40, 'categoria' => 'datos-personales'],
            ['id' => 41, 'categoria' => 'datos-personales'],
            ['id' => 42, 'categoria' => 'datos-personales'],
            ['id' => 43, 'categoria' => 'datos-economicos'],
            ['id' => 44, 'categoria' => 'deudas'],
            ['id' => 45, 'categoria' => 'datos-personales'],
            ['id' => 46, 'categoria' => 'datos-personales'],
            ['id' => 47, 'categoria' => 'vivienda'],
            ['id' => 48, 'categoria' => 'hijos'],
            ['id' => 49, 'categoria' => 'datos-personales'],
            ['id' => 50, 'categoria' => 'deudas'],
            ['id' => 51, 'categoria' => 'deudas'],
            ['id' => 52, 'categoria' => 'hijos'],
            ['id' => 53, 'categoria' => 'hijos'],
            ['id' => 54, 'categoria' => 'hijos'],
            ['id' => 55, 'categoria' => 'hijos'],
            ['id' => 56, 'categoria' => 'datos-personales'],
            ['id' => 57, 'categoria' => 'datos-personales'],
        ];

        foreach ($data as $item) {
            DB::table('questions')
                ->where('id', $item['id'])
                ->update(['categoria' => $item['categoria']]);
        }
    }
}
