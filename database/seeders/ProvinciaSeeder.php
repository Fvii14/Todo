<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciaSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = [
            ['codigo_provincia' => '01', 'nombre_provincia' => 'Álava', 'id_ccaa' => 8],
            ['codigo_provincia' => '02', 'nombre_provincia' => 'Albacete', 'id_ccaa' => 7],
            ['codigo_provincia' => '03', 'nombre_provincia' => 'Alicante', 'id_ccaa' => 4],
            ['codigo_provincia' => '04', 'nombre_provincia' => 'Almería', 'id_ccaa' => 1],
            ['codigo_provincia' => '05', 'nombre_provincia' => 'Ávila', 'id_ccaa' => 5],
            ['codigo_provincia' => '06', 'nombre_provincia' => 'Badajoz', 'id_ccaa' => 10],
            ['codigo_provincia' => '07', 'nombre_provincia' => 'Islas Baleares', 'id_ccaa' => 13],
            ['codigo_provincia' => '08', 'nombre_provincia' => 'Barcelona', 'id_ccaa' => 2],
            ['codigo_provincia' => '09', 'nombre_provincia' => 'Burgos', 'id_ccaa' => 5],
            ['codigo_provincia' => '10', 'nombre_provincia' => 'Cáceres', 'id_ccaa' => 10],
            ['codigo_provincia' => '11', 'nombre_provincia' => 'Cádiz', 'id_ccaa' => 1],
            ['codigo_provincia' => '12', 'nombre_provincia' => 'Castellón', 'id_ccaa' => 4],
            ['codigo_provincia' => '13', 'nombre_provincia' => 'Ciudad Real', 'id_ccaa' => 7],
            ['codigo_provincia' => '14', 'nombre_provincia' => 'Córdoba', 'id_ccaa' => 1],
            ['codigo_provincia' => '15', 'nombre_provincia' => 'A Coruña', 'id_ccaa' => 6],
            ['codigo_provincia' => '16', 'nombre_provincia' => 'Cuenca', 'id_ccaa' => 7],
            ['codigo_provincia' => '17', 'nombre_provincia' => 'Girona', 'id_ccaa' => 2],
            ['codigo_provincia' => '18', 'nombre_provincia' => 'Granada', 'id_ccaa' => 1],
            ['codigo_provincia' => '19', 'nombre_provincia' => 'Guadalajara', 'id_ccaa' => 7],
            ['codigo_provincia' => '20', 'nombre_provincia' => 'Guipúzcoa', 'id_ccaa' => 8],
            ['codigo_provincia' => '21', 'nombre_provincia' => 'Huelva', 'id_ccaa' => 1],
            ['codigo_provincia' => '22', 'nombre_provincia' => 'Huesca', 'id_ccaa' => 11],
            ['codigo_provincia' => '23', 'nombre_provincia' => 'Jaén', 'id_ccaa' => 1],
            ['codigo_provincia' => '24', 'nombre_provincia' => 'León', 'id_ccaa' => 5],
            ['codigo_provincia' => '25', 'nombre_provincia' => 'Lleida', 'id_ccaa' => 2],
            ['codigo_provincia' => '26', 'nombre_provincia' => 'La Rioja', 'id_ccaa' => 17],
            ['codigo_provincia' => '27', 'nombre_provincia' => 'Lugo', 'id_ccaa' => 6],
            ['codigo_provincia' => '28', 'nombre_provincia' => 'Madrid', 'id_ccaa' => 3],
            ['codigo_provincia' => '29', 'nombre_provincia' => 'Málaga', 'id_ccaa' => 1],
            ['codigo_provincia' => '30', 'nombre_provincia' => 'Murcia', 'id_ccaa' => 12],
            ['codigo_provincia' => '31', 'nombre_provincia' => 'Navarra', 'id_ccaa' => 16],
            ['codigo_provincia' => '32', 'nombre_provincia' => 'Ourense', 'id_ccaa' => 6],
            ['codigo_provincia' => '33', 'nombre_provincia' => 'Asturias', 'id_ccaa' => 15],
            ['codigo_provincia' => '34', 'nombre_provincia' => 'Palencia', 'id_ccaa' => 5],
            ['codigo_provincia' => '35', 'nombre_provincia' => 'Las Palmas', 'id_ccaa' => 9],
            ['codigo_provincia' => '36', 'nombre_provincia' => 'Pontevedra', 'id_ccaa' => 6],
            ['codigo_provincia' => '37', 'nombre_provincia' => 'Salamanca', 'id_ccaa' => 5],
            ['codigo_provincia' => '38', 'nombre_provincia' => 'Santa Cruz de Tenerife', 'id_ccaa' => 9],
            ['codigo_provincia' => '39', 'nombre_provincia' => 'Cantabria', 'id_ccaa' => 14],
            ['codigo_provincia' => '40', 'nombre_provincia' => 'Segovia', 'id_ccaa' => 5],
            ['codigo_provincia' => '41', 'nombre_provincia' => 'Sevilla', 'id_ccaa' => 1],
            ['codigo_provincia' => '42', 'nombre_provincia' => 'Soria', 'id_ccaa' => 5],
            ['codigo_provincia' => '43', 'nombre_provincia' => 'Tarragona', 'id_ccaa' => 2],
            ['codigo_provincia' => '44', 'nombre_provincia' => 'Teruel', 'id_ccaa' => 11],
            ['codigo_provincia' => '45', 'nombre_provincia' => 'Toledo', 'id_ccaa' => 7],
            ['codigo_provincia' => '46', 'nombre_provincia' => 'Valencia', 'id_ccaa' => 4],
            ['codigo_provincia' => '47', 'nombre_provincia' => 'Valladolid', 'id_ccaa' => 5],
            ['codigo_provincia' => '48', 'nombre_provincia' => 'Vizcaya', 'id_ccaa' => 8],
            ['codigo_provincia' => '49', 'nombre_provincia' => 'Zamora', 'id_ccaa' => 5],
            ['codigo_provincia' => '50', 'nombre_provincia' => 'Zaragoza', 'id_ccaa' => 11],
            ['codigo_provincia' => '51', 'nombre_provincia' => 'Ceuta', 'id_ccaa' => 18],
            ['codigo_provincia' => '52', 'nombre_provincia' => 'Melilla', 'id_ccaa' => 19],
        ];

        foreach ($provincias as $provincia) {
            DB::table('provincia')->updateOrInsert(
                ['codigo_provincia' => $provincia['codigo_provincia']],
                [
                    'nombre_provincia' => $provincia['nombre_provincia'],
                    'id_ccaa' => $provincia['id_ccaa'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
