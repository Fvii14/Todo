<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QQCuestionariosConvivientesSeeder extends Seeder
{
    public function run(): void
    {
        // PAV Canarias-67, PAV Aragon-71, PAV Castilla y Leon- 72, BAJ Canarias-48, BAJ Murcia- 57, BAJ Madrid-58, PAV Madrid- 77, PAV La Rioja-68, PAV ANDALUCIA-75, BAJ EXTREMADURA-50  Y PAV Galicia-79
        $questionnaireIds = [50, 57, 58, 67, 68, 71, 72, 75, 77, 79];
        $questionIds = [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152];

        $now = now();
        $data = [];

        foreach ($questionnaireIds as $questionnaireId) {
            foreach ($questionIds as $order => $questionId) {
                $data[] = [
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $questionId,
                    'orden' => $order + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('questionnaire_questions')->insert($data);

        $registros = [
            // BAJ
            // BAJ Cataluña(46)----
            ['questionnaire_id' => 46, 'question_ids' => [177, 170, 171, 40, 178, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // BAJ VALENCIA(47)-----
            ['questionnaire_id' => 47, 'question_ids' => [177, 170, 171, 40, 119, 34, 143, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152, 146]],
            // BAJ CASTILLA Y LEON (53)------
            ['questionnaire_id' => 53, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // BAJ BALEARES(55)----------
            ['questionnaire_id' => 55, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // BAJ GALICIA (60)---------
            ['questionnaire_id' => 60, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // BAJ ASTURIAS(51)--------------------
            ['questionnaire_id' => 51, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // BAJ ANDALUCIA(56)--------------------
            ['questionnaire_id' => 56, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // BAJ ANDALUCIA(59)--------------------
            ['questionnaire_id' => 59, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // BAJ aragon(52)--------------------
            ['questionnaire_id' => 52, 'question_ids' => [177, 170, 171, 40, 119, 34, 143, 144, 127, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],

            // PAV
            // PAV CATALUÑA (65)----------
            ['questionnaire_id' => 65, 'question_ids' => [177, 170, 171, 40, 178, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // PAV C. VALENCIANA (66)-----------
            ['questionnaire_id' => 66, 'question_ids' => [177, 170, 171, 40, 119, 34, 143, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152, 146]],
            // PAV CASTILLA Y LA MANCHA (73)-----------
            ['questionnaire_id' => 73, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // PAV BALEARES (74)---------------
            ['questionnaire_id' => 74, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // PAV MURCIA(76)--------------------
            ['questionnaire_id' => 76, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // PAV ASTURIAS(70)--------------------
            ['questionnaire_id' => 70, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // PAV EXTREMADURA(69)--------------------
            ['questionnaire_id' => 69, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // PAV NAVARRA(64)--------------------
            ['questionnaire_id' => 64, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
            // PAV PAIS VASCO(61)--------------------
            ['questionnaire_id' => 61, 'question_ids' => [177, 170, 171, 40, 119, 34, 144, 156, 157, 172, 173, 184, 179, 180, 148, 149, 150, 151, 152]],
        ];

        // Inserción
        foreach ($registros as $registro) {
            $order = 1; // reiniciar orden por cada cuestionario

            foreach ($registro['question_ids'] as $questionId) {
                DB::table('questionnaire_questions')->insert([
                    'questionnaire_id' => $registro['questionnaire_id'],
                    'question_id' => $questionId,
                    'orden' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $order++;
            }
        }
    }
}
