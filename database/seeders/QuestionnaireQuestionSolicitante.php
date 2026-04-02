<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireQuestionSolicitante extends Seeder
{
    public function run(): void
    {
        // Los slugs de las preguntas que quieres meter
        $slugs = [
            'solo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'situacion_laboral',
            'pertenece-grupo-vulnerable-solicitante',
            'grupo_vulnerable_solicitante',
            'porcentaje_discapacidad',
            'movilidad_reducida',
            'iban',
            'solicitante-ha-hecho-renta',
            'ha_solicitado_otra_ayuda',
            'tipo_ayuda_alquiler_solicitada',
            'fecha_solicitud_otra_ayuda_solicitada',
            'otra_ayuda_concedida',
            'cantidad_concedida_otra_ayuda',
        ];

        // para el 85 que es baj valencia añadir la nacionalidad_obtenida_3_ultimos
        $slugs_baj_val = [
            // 'tipo_via',
            // 'nombre_via',
            // 'numero_domicilio',
            // 'bloque',
            // 'portal',
            // 'escalera',
            // 'piso',
            // 'puerta',
            // 'numero_soporte_documento',
            // 'fecha_validez_documento',
            'nacionalidad',
            'iban',
            'pertenece-grupo-vulnerable-solicitante',
            'grupo_vulnerable_solicitante',
            'nacionalidad_obtenida_3_ultimos',
            'ha_solicitado_otra_ayuda',
            'tipo_ayuda_alquiler_solicitada',
            'fecha_solicitud_otra_ayuda_solicitada',
            'otra_ayuda_concedida',
            'cantidad_concedida_otra_ayuda',

        ];

        // 1️⃣ Sacamos los question_id
        $questions = DB::table('questions')
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        // Validación: si falta alguna pregunta, mostrar error
        foreach ($slugs as $slug) {
            if (! isset($questions[$slug])) {
                throw new \Exception("❌ ERROR: No se ha encontrado la pregunta con slug: '{$slug}' en la tabla questions");
            }
        }

        // 2️⃣ Sacamos todos los questionnaire_id tipo 'solicitud' (excepto ayuda ID 3)
        $questionnaireIds = DB::table('questionnaires')
            ->join('ayudas', 'ayudas.id', '=', 'questionnaires.ayuda_id')
            ->where('questionnaires.tipo', 'solicitud')
            ->pluck('questionnaires.id');

        // 3️⃣ Insertamos en questionnaire_questions
        foreach ($questionnaireIds as $questionnaireId) {
            $order = 1;

            foreach ($slugs as $slug) {
                $questionId = $questions[$slug]->id;

                DB::table('questionnaire_questions')->insert([
                    'questionnaire_id' => $questionnaireId,
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
