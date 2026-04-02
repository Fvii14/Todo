<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rellena codigo_hubspot (Ref. HS) en ayudas según el mapeo HubSpot.
     * Ejecutar después de add_codigo_to_ayudas_table.
     */
    public function up(): void
    {
        $codigos = [
            1 => 'A1P-UNK-25',
            452 => 'A5a-Mad-25',
            43 => 'ATB-Gal-25',
            25 => 'BAJ-And-25',
            17 => 'BAJ-Ara-25',
            23 => 'BAJ-Bal-25',
            315 => 'BAJ-Can-25',
            19 => 'BAJ-CyL-25',
            458 => 'BAJ-CM-25',
            441 => 'BAJ-Cat-25',
            440 => 'BAJ-Mad-25',
            438 => 'BAJ-CV-25',
            51 => 'BAJ-CV-23',
            33 => 'BAJ-Gal-25',
            11 => 'BAJ-LR-25',
            27 => 'BAJ-Mur-25',
            450 => 'Bc-CyL-25',
            446 => 'DPM-UNK-25',
            2 => 'IMV-UNK-25',
            24 => 'PAV-And-25',
            16 => 'PAV-Ara-25',
            459 => 'PAV-Ara-25',
            54 => 'PAV-Ara-24',
            14 => 'PAV-Ast-25',
            22 => 'PAV-Bal-25',
            436 => 'PAV-Bal-25',
            8 => 'PAV-Can-25',
            437 => 'PAV-Can-25',
            53 => 'PAV-Can-24',
            457 => 'PAV-CM-25',
            18 => 'PAV-CyL-25',
            445 => 'PAV-CyL-25',
            55 => 'PAV-CyL-24',
            20 => 'PAV-CM-25',
            5 => 'PAV-36-Cat-24',
            48 => 'PAV-36-Cat-24',
            45 => 'PAV +36 -65-Cat-25',
            47 => 'PAV +36 -65-Cat-24',
            46 => 'PAV-Cat-25',
            442 => 'PAV -36-Cat-25',
            443 => 'PAV +36 -65-Cat-25',
            435 => 'PAV-Mad-25',
            334 => 'PAV-Mad-25',
            434 => 'PAV-Mad-25',
            7 => 'PAV-CV-25',
            324 => 'PAV-CV-25',
            439 => 'PAV-CV-25',
            52 => 'PAV-CV-23',
            50 => 'PAV-CV-24',
            12 => 'PAV-Ext-25',
            32 => 'PAV-Gal-25',
            10 => 'PAV-LR-25',
            28 => 'PAV-Mad-25',
            26 => 'PAV-Mur-25',
            49 => 'PAV-Mur-24',
        ];

        foreach ($codigos as $ayudaId => $codigo) {
            DB::table('ayudas')->where('id', $ayudaId)->update(['codigo_hubspot' => $codigo]);
        }
    }

    public function down(): void
    {
        DB::table('ayudas')->whereNotNull('codigo_hubspot')->update(['codigo_hubspot' => null]);
    }
};
