<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;

class QuestionService
{
    public function __construct(
        private LocationService $locationService
    ) {}

    /**
     * Obtiene preguntas de un cuestionario con orden
     */
    public function getQuestionsByQuestionnaire(int $questionnaireId): Collection
    {
        return Question::forQuestionnaire($questionnaireId)->get();
    }

    /**
     * Obtiene IDs de preguntas de un cuestionario
     */
    public function getQuestionIdsByQuestionnaire(int $questionnaireId): Collection
    {
        return Question::questionIdsByQuestionnaire($questionnaireId)->pluck('id');
    }

    /**
     * Obtiene una pregunta por ID
     */
    public function getQuestionById(int $id): ?Question
    {
        return Question::find($id);
    }

    public function getQuestionsByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return Question::whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Obtiene una pregunta por slug
     */
    public function getQuestionBySlug(string $slug): ?Question
    {
        return Question::bySlug($slug)->first();
    }

    /**
     * Obtiene el ID de una pregunta por slug
     */
    public function getQuestionIdBySlug(string $slug): ?int
    {
        return Question::bySlug($slug)->value('id');
    }

    /**
     * Obtiene opciones de una pregunta, manejando casos especiales
     */
    public function getQuestionOptions(Question $question): array
    {
        if ($question->slug === 'provincia') {
            return $this->locationService->getProvincias()->pluck('nombre_provincia', 'id')->toArray();
        } elseif ($question->slug === 'municipio') {
            // Los municipios se cargarán por JS
            return [];
        } elseif (is_string($question->options)) {
            return json_decode($question->options, true) ?? [];
        } elseif (is_array($question->options)) {
            return $question->options;
        }

        return [];
    }

    /**
     * Obtiene regex para validación de preguntas
     */
    public function getRegexForQuestions(array $questionIds): Collection
    {
        return Question::withRegex($questionIds)->get()->keyBy('question_id');
    }

    /**
     * Formatea el subtext de una pregunta convirtiendo URLs a enlaces HTML
     */
    public function formatQuestionSubtext(?string $subtext): ?string
    {
        if (! $subtext) {
            return null;
        }

        $pattern = '/https?:\/\/[^\s]+/i';
        $escaped = e($subtext);

        return preg_replace(
            $pattern,
            '<a href="$0" target="_blank" rel="noopener noreferrer" class="text-indigo-600 underline">enlace</a>',
            $escaped
        );
    }
}
