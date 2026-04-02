<?php

namespace Database\Seeders;

use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Questionnaire::create([
            'name' => 'Formulario Post-Collector',
            'redirect_url' => null,
            'active' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'ayuda_id' => null,
            'tipo' => 'collector',
            'slug' => 'form_post_collector',
        ]);
    }
}
