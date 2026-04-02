<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaDocumentoSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        // Inserciones existentes...
        DB::table('ayuda_documentos')->insert([
            // Ayuda 100€ por hijo
            [
                'ayuda_id' => 1,
                'documento_id' => 1,
                'es_obligatorio' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 1,
                'documento_id' => 2,
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 1,
                'documento_id' => 3,
                'es_obligatorio' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Ingreso Mínimo Vital (I.M.V)
            [
                'ayuda_id' => 2,
                'documento_id' => 12,
                'es_obligatorio' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 13,
                'es_obligatorio' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 14,
                'es_obligatorio' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 15,
                'es_obligatorio' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 16,
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 17,
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 18,
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 19,
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayuda_id' => 2,
                'documento_id' => 20,
                'es_obligatorio' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Nuevas inserciones para ayudas 3 a 41 ayudas de alquiler
        $obligatorios = [3, 5, 6, 7, 8, 9, 51];
        $noObligatorios = [17, 31, 18, 21, 27, 22, 23, 20, 24, 56, 52, 54, 55, 56, 57, 30, 31, 32, 29];
        $now = Carbon::now();

        $nuevos = [];

        for ($ayudaId = 3; $ayudaId <= 41; $ayudaId++) {
            // Si es la ayuda con ID 17 (Bono Alquiler Joven Aragón), excluimos el documento con ID 8
            if ($ayudaId == 17) {
                $obligatorios = array_filter($obligatorios, function ($docId) {
                    return $docId !== 8; // Excluir documento con ID 8
                });
            }

            // Insertamos documentos obligatorios
            foreach ($obligatorios as $docId) {
                $nuevos[] = [
                    'ayuda_id' => $ayudaId,
                    'documento_id' => $docId,
                    'es_obligatorio' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Insertamos documentos no obligatorios
            foreach ($noObligatorios as $docId) {
                $nuevos[] = [
                    'ayuda_id' => $ayudaId,
                    'documento_id' => $docId,
                    'es_obligatorio' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('ayuda_documentos')->insert($nuevos);
    }
}
