<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudasCcaaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id' => 4, 'ccaa_id' => 2],
            ['id' => 5, 'ccaa_id' => 2],
            ['id' => 6, 'ccaa_id' => 4],
            ['id' => 7, 'ccaa_id' => 4],
            ['id' => 8, 'ccaa_id' => 9],
            ['id' => 9, 'ccaa_id' => 9],
            ['id' => 10, 'ccaa_id' => 17],
            ['id' => 11, 'ccaa_id' => 17],
            ['id' => 12, 'ccaa_id' => 10],
            ['id' => 13, 'ccaa_id' => 10],
            ['id' => 14, 'ccaa_id' => 15],
            ['id' => 15, 'ccaa_id' => 15],
            ['id' => 16, 'ccaa_id' => 11],
            ['id' => 17, 'ccaa_id' => 11],
            ['id' => 18, 'ccaa_id' => 5],
            ['id' => 19, 'ccaa_id' => 5],
            ['id' => 20, 'ccaa_id' => 7],
            ['id' => 21, 'ccaa_id' => 7],
            ['id' => 22, 'ccaa_id' => 13],
            ['id' => 23, 'ccaa_id' => 13],
            ['id' => 24, 'ccaa_id' => 1],
            ['id' => 25, 'ccaa_id' => 1],
            ['id' => 26, 'ccaa_id' => 12],
            ['id' => 27, 'ccaa_id' => 12],
            ['id' => 28, 'ccaa_id' => 3],
            ['id' => 29, 'ccaa_id' => 3],
            ['id' => 30, 'ccaa_id' => 14],
            ['id' => 31, 'ccaa_id' => 14],
            ['id' => 32, 'ccaa_id' => 6],
            ['id' => 33, 'ccaa_id' => 6],
            ['id' => 34, 'ccaa_id' => 8],
            ['id' => 35, 'ccaa_id' => 8],
            ['id' => 36, 'ccaa_id' => 18],
            ['id' => 37, 'ccaa_id' => 18],
            ['id' => 38, 'ccaa_id' => 19],
            ['id' => 39, 'ccaa_id' => 19],
            ['id' => 40, 'ccaa_id' => 16],
            ['id' => 41, 'ccaa_id' => 16],
        ];

        foreach ($data as $entry) {
            DB::table('ayudas')
                ->where('id', $entry['id'])
                ->update(['ccaa_id' => $entry['ccaa_id']]);
        }
    }
}
