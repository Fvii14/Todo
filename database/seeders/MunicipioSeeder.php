<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipioSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/municipios_espana_2025.csv');

        $handle = fopen($file, 'r');
        if (! $handle) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        DB::table('municipio')->delete();

        // Creamos un mapa de codigo_provincia (p.ej. '08') => id
        $provincias = DB::table('provincia')
            ->get()
            ->mapWithKeys(function ($provincia) {
                $codigo = str_pad($provincia->codigo_provincia, 2, '0', STR_PAD_LEFT);

                return [$codigo => $provincia->id];
            });

        // Saltar la cabecera
        fgetcsv($handle);

        $data = [];
        $now = now();
        $batchSize = 1000;

        while (($row = fgetcsv($handle)) !== false) {
            [$cpro, $cmun, $nombre] = $row;

            $codigoProvincia = str_pad($cpro, 2, '0', STR_PAD_LEFT);

            if (! isset($provincias[$codigoProvincia])) {
                continue;
            }

            $data[] = [
                'provincia_id' => $provincias[$codigoProvincia], // usamos el ID real
                'codigo_municipio' => str_pad($cmun, 3, '0', STR_PAD_LEFT),
                'nombre_municipio' => trim($nombre),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($data) >= $batchSize) {
                DB::table('municipio')->insert($data);
                $data = [];
            }
        }

        if (! empty($data)) {
            DB::table('municipio')->insert($data);
        }

        fclose($handle);
    }
}
