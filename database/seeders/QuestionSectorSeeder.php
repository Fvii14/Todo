<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id' => 1, 'sector' => 'alquiler'],
            ['id' => 2, 'sector' => 'alquiler'],
            ['id' => 3, 'sector' => 'alquiler'],
            ['id' => 4, 'sector' => 'alquiler'],
            ['id' => 5, 'sector' => 'alquiler'],
            ['id' => 6, 'sector' => 'alquiler'],
            ['id' => 7, 'sector' => 'alquiler'],
            ['id' => 8, 'sector' => 'alquiler'],
            ['id' => 9, 'sector' => 'alquiler'],
            ['id' => 10, 'sector' => 'alquiler'],
            ['id' => 11, 'sector' => 'alquiler'],
            ['id' => 12, 'sector' => 'alquiler'],
            ['id' => 13, 'sector' => 'alquiler'],
            ['id' => 14, 'sector' => 'alquiler'],
            ['id' => 15, 'sector' => 'alquiler'],
            ['id' => 16, 'sector' => 'alquiler'],
            ['id' => 17, 'sector' => 'alquiler'],
            ['id' => 18, 'sector' => 'alquiler'],
            ['id' => 19, 'sector' => 'alquiler'],
            ['id' => 20, 'sector' => 'alquiler'],
            ['id' => 21, 'sector' => 'alquiler'],
            ['id' => 22, 'sector' => 'alquiler'],
            ['id' => 23, 'sector' => 'alquiler'],
            ['id' => 24, 'sector' => 'alquiler'],
            ['id' => 25, 'sector' => 'alquiler'],
            ['id' => 26, 'sector' => 'alquiler'],
            ['id' => 27, 'sector' => 'alquiler'],
            ['id' => 28, 'sector' => 'alquiler'],
            ['id' => 29, 'sector' => 'alquiler'],
            ['id' => 30, 'sector' => 'alquiler'],
            ['id' => 31, 'sector' => 'alquiler'],
            ['id' => 32, 'sector' => 'alquiler'],
            ['id' => 33, 'sector' => 'collector'],
            ['id' => 34, 'sector' => 'collector'],
            ['id' => 35, 'sector' => 'collector'],
            ['id' => 36, 'sector' => 'collector'],
            ['id' => 37, 'sector' => 'collector'],
            ['id' => 38, 'sector' => 'collector'],
            ['id' => 39, 'sector' => 'collector'],
            ['id' => 40, 'sector' => 'collector'],
            ['id' => 41, 'sector' => 'collector'],
            ['id' => 42, 'sector' => 'collector'],
            ['id' => 43, 'sector' => 'collector'],
            ['id' => 44, 'sector' => 'collector'],
            ['id' => 45, 'sector' => 'collector'],
            ['id' => 46, 'sector' => 'collector'],
            ['id' => 47, 'sector' => 'collector'],
            ['id' => 48, 'sector' => 'familia'],
            ['id' => 49, 'sector' => 'collector'],
            ['id' => 50, 'sector' => 'collector'],
            ['id' => 51, 'sector' => 'collector'],
            ['id' => 52, 'sector' => 'familia'],
            ['id' => 53, 'sector' => 'familia'],
            ['id' => 54, 'sector' => 'familia'],
            ['id' => 55, 'sector' => 'familia'],
            ['id' => 56, 'sector' => 'collector'],
            ['id' => 57, 'sector' => 'collector'],

        ];

        foreach ($data as $item) {
            DB::table('questions')
                ->where('id', $item['id'])
                ->update(['sector' => $item['sector']]);
        }
    }
}
