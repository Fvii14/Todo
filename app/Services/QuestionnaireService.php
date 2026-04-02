<?php

namespace App\Services;

use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use Illuminate\Support\Collection;

class QuestionnaireService
{
    /**
     * Obtiene un cuestionario por ID con relaciones opcionales
     */
    public function getQuestionnaireById(int $id, array $with = []): Questionnaire
    {
        $query = Questionnaire::query();

        if (! empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }

    /**
     * Obtiene un cuestionario por slug
     */
    public function getQuestionnaireBySlug(string $slug): ?Questionnaire
    {
        return Questionnaire::bySlug($slug)->first();
    }

    /**
     * Obtiene condiciones de visibilidad de preguntas para un cuestionario
     */
    public function getQuestionConditions(int $questionnaireId, array $questionIds): Collection
    {
        return QuestionCondition::where('questionnaire_id', $questionnaireId)
            ->where(function ($query) use ($questionIds) {
                $query->whereIn('question_id', $questionIds)
                    ->orWhereIn('next_question_id', $questionIds);
            })
            ->get();
    }

    /**
     * Verifica si un cuestionario es de tipo collector
     */
    public function isCollectorQuestionnaire(int $questionnaireId): bool
    {
        $formPostCollector = Questionnaire::bySlug('form_post_collector')->value('id');
        $formCollector = Questionnaire::bySlug('form_collector')->value('id');

        return $questionnaireId == $formPostCollector || $questionnaireId == $formCollector;
    }
}
