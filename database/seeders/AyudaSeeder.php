<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaSeeder extends Seeder
{
    public function run()
    {
        DB::table('ayudas')->insert([
            [
                'nombre_ayuda' => 'Cheque bebé',
                'sector' => 'familia',
                'create_time' => null,
                'questionnaire_id' => 2,
                'presupuesto' => 400000000,
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'description' => 'La ayuda cheque bebé es una prestación mensual que pueden recibir las madres trabajadoras con hijos menores de 3 años. Se solicita a través de Hacienda y puede alcanzar hasta 1.200€ al año por cada hijo.',
                'organo_id' => 20,
                'activo' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cuantia_usuario' => 1200,
                'pago' => 1,
                'slug' => 'ayuda_100_por_hijo',
            ],
            [
                'nombre_ayuda' => 'Ingreso Mínimo Vital (I.M.V)',
                'sector' => 'familia',
                'create_time' => null,
                'questionnaire_id' => 42,
                'presupuesto' => 3000000000,
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'description' => 'El Ingreso Mínimo Vital (IMV) es una ayuda estatal permanente dirigida a hogares en situación de vulnerabilidad económica. Garantiza un ingreso mensual mínimo adaptado al tamaño de la unidad de convivencia, pudiendo incluir complementos por hijos o discapacidad. Se solicita a través de la Seguridad Social. La cuantía puede variar desde los 658€ al mes hasta 1421,50€ por un periodo de tiempo indeterminado',
                'organo_id' => 20,
                'activo' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cuantia_usuario' => 17388,
                'pago' => 1,
                'slug' => 'ingreso_minimo_vital',
            ],
        ]);
    }
}
