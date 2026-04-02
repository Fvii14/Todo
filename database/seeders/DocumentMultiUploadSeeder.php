<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentMultiUploadSeeder extends Seeder
{
    public function run(): void
    {
        $slugsMulti = [
            'documento_identidad',
            'padron-colectivo',
            'justificante_pago',
            'otros_ingresos',
            'otros_documentos',
            'contrato-trabajo',
            'declaracion_renta',
            'libro_familia',
            'certificado_discapacidad',
            'sentencia_custodia',
            'acreditacion_monoparental',
            'contrato-subvencionado',
            'escritura-propiedad',
            'acreditacion_vulnerabilidad',
            'dni_arrendador',
            'dni_conyuge',
            'dni_tutor',
            'dni_otro_titular',
            'resolucion_imv',
        ];

        DB::table('documents')
            ->whereIn('slug', $slugsMulti)
            ->update(['multi_upload' => true]);

    }
}
