<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireQuestionsSeeder3 extends Seeder
{
    public function run()
    {
        $postCollectorFormId = Questionnaire::where('slug', 'form_post_collector')->first()->id;
        DB::table('questionnaire_questions')->insert([
            [
                'questionnaire_id' => $postCollectorFormId,
                'question_id' => Question::where('slug', 'provincia')->first()->id,
                'orden' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $postCollectorFormId,
                'question_id' => Question::where('slug', 'municipio')->first()->id,
                'orden' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $postCollectorFormId,
                'question_id' => Question::where('slug', 'tiene_hijos_o_pronto')->first()->id,
                'orden' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $postCollectorFormId,
                'question_id' => Question::where('slug', 'vives_alquiler')->first()->id,
                'orden' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $postCollectorFormId,
                'question_id' => Question::where('slug', 'quieres_vives_alquiler')->first()->id,
                'orden' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);

        DB::table('question_conditions')->insert([
            [
                'question_id' => Question::where('slug', 'vives_alquiler')->value('id'),
                'questionnaire_id' => Questionnaire::where('slug', 'form_post_collector')->value('id'),
                'condition' => json_encode([0]),
                'next_question_id' => Question::where('slug', 'quieres_vives_alquiler')->value('id'),
                'created_at' => Carbon::now(),
            ],
        ]);

    }
}
