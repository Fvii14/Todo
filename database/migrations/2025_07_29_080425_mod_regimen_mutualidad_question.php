<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('questions')
            ->where('slug', 'regimen-mutualidad')
            ->update([
                'type' => 'select',
                'options' => json_encode([
                    'SEGURIDAD SOCIAL',
                    'PRESTACIÓN SEPE',
                    'MUFACE',
                    'MUGEJU',
                    'ISFAS',
                    'AMIC-ASOC.MUTUALISTA INGENIEROS CIVILES MDAD. CAJA FAMILIAR MÉDICOS DE CANTABRÍA',
                    'HNA-HERMANDAD NACIONAL DE ARQUITECTOS',
                    'PS.PROCURADORES DE LOS TRIB. ESPAÑOLES',
                    'MUPITI-PERITOS E ING. TEC INDUSTRIALES',
                    'P.S. GESTORES ADMINISTRATIVOS',
                    'PREV SOCIAL DELS ADVOCATS DE CATALUNYA',
                    'MÉDICA DE CATALUNYA I BALEARS, PRE SOCIAL',
                    'MUTUALIDAD GENERAL DE LA ABOGACÍA',
                    "COLLEGI OF.D'ENGINYERS IND DE CATALUNYA",
                    'PREVISIÓN SOCIAL DE QUÍMICOS ESPAÑOLES',
                    'PREMAAT-APAREJADORES Y ARQUITECTOS TECN',
                ]),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('questions')
            ->where('slug', 'regimen-mutualidad')
            ->update([
                'type' => 'string',
                'options' => json_encode([]),
                'updated_at' => now(),
            ]);
    }
};
