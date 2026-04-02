<?php

namespace App\Services;

use App\Models\Answer;
use Illuminate\Support\Collection;

class AnswerService
{
    /**
     * Obtiene respuestas de un usuario para un cuestionario específico
     */
    public function getAnswersByUserAndQuestionnaire(int $userId, int $questionnaireId): Collection
    {
        return Answer::byUser($userId)
            ->forQuestionnaire($questionnaireId)
            ->withoutConviviente()
            ->pluck('answer', 'question_id');
    }

    /**
     * Obtiene respuestas de un usuario para IDs de preguntas específicas
     */
    public function getAnswersByUserAndQuestionIds(int $userId, array $questionIds, ?int $convivienteId = null): Collection
    {
        $query = Answer::byUser($userId)->byQuestions($questionIds);

        if ($convivienteId === null) {
            $query->withoutConviviente();
        } else {
            $query->byConviviente($convivienteId);
        }

        return $query->pluck('answer', 'question_id');
    }

    /**
     * Obtiene una respuesta específica de un usuario
     */
    public function getAnswerByUserAndQuestion(int $userId, int $questionId, ?int $convivienteId = null): ?Answer
    {
        $query = Answer::byUser($userId)->byQuestion($questionId);

        if ($convivienteId === null) {
            $query->withoutConviviente();
        } else {
            $query->byConviviente($convivienteId);
        }

        return $query->first();
    }

    /**
     * Crea una nueva respuesta
     */
    public function createAnswer(array $data): Answer
    {
        return Answer::create($data);
    }

    /**
     * Actualiza o crea una respuesta
     */
    public function updateOrCreateAnswer(array $attributes, array $values): Answer
    {
        return Answer::updateOrCreate($attributes, $values);
    }

    /**
     * Elimina respuestas de un usuario para una pregunta específica
     */
    public function deleteAnswersByUserAndQuestion(int $userId, int $questionId, ?int $convivienteId = null): bool
    {
        $query = Answer::byUser($userId)->byQuestion($questionId);

        if ($convivienteId === null) {
            $query->withoutConviviente();
        } else {
            $query->byConviviente($convivienteId);
        }

        return $query->delete() > 0;
    }

    public function deleteAnswersByUserAndQuestions(int $userId, array $questionIds, ?int $convivienteId = null): void
    {
        if (empty($questionIds)) {
            return;
        }

        $query = Answer::byUser($userId)->byQuestions($questionIds);

        if ($convivienteId === null) {
            $query->withoutConviviente();
        } else {
            $query->byConviviente($convivienteId);
        }

        $query->delete();
    }

    /**
     * Realiza upsert de múltiples respuestas
     */
    public function upsertAnswers(array $answers, int $userId): void
    {
        if (empty($answers)) {
            return;
        }

        Answer::upsert(
            $answers,
            ['user_id', 'question_id'],
            ['answer', 'updated_at']
        );
    }

    /**
     * Sincroniza respuestas especiales entre preguntas relacionadas
     * - Pregunta 4 ↔ 35 (domicilio)
     * - Pregunta 43 → 86
     * - Pregunta 26 → 2 (en show)
     */
    public function syncSpecialAnswers(int $userId, array $answers): void
    {
        // Sincronización 4 ↔ 35
        if (isset($answers[35])) {
            $existingAnswerFor4 = $this->getAnswerByUserAndQuestion($userId, 4);
            if (! $existingAnswerFor4) {
                $this->createAnswer([
                    'user_id' => $userId,
                    'question_id' => 4,
                    'answer' => $answers[35],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'conviviente_id' => null,
                ]);
            }
        }

        if (isset($answers[4])) {
            $existingAnswerFor35 = $this->getAnswerByUserAndQuestion($userId, 35);
            if (! $existingAnswerFor35) {
                $this->createAnswer([
                    'user_id' => $userId,
                    'question_id' => 35,
                    'answer' => $answers[4],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'conviviente_id' => null,
                ]);
            }
        }

        // Sincronización 43 → 86
        if (isset($answers[43])) {
            $existingAnswerFor86 = $this->getAnswerByUserAndQuestion($userId, 86);
            if (! $existingAnswerFor86) {
                $this->createAnswer([
                    'user_id' => $userId,
                    'question_id' => 86,
                    'answer' => $answers[43],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'conviviente_id' => null,
                ]);
            }
        }
    }

    /**
     * Sincroniza respuesta de pregunta 26 a pregunta 2 (usado en show)
     */
    public function syncAnswer26To2(int $userId): void
    {
        $answerQuestion26 = $this->getAnswerByUserAndQuestion($userId, 26);

        if ($answerQuestion26 && $answerQuestion26->answer !== null) {
            $this->updateOrCreateAnswer(
                ['user_id' => $userId, 'question_id' => 2, 'conviviente_id' => null],
                ['answer' => $answerQuestion26->answer, 'updated_at' => now()]
            );
        }
    }

    /**
     * Guarda múltiples respuestas, eliminando las anteriores para cada pregunta
     */
    public function saveAnswers(array $answers, int $userId): void
    {
        // Filtrar respuestas nulas o vacías
        $filteredAnswers = array_filter($answers, function ($value) {
            return $value !== null && $value !== '';
        });

        foreach ($filteredAnswers as $questionId => $answer) {
            // Eliminar respuestas anteriores
            $this->deleteAnswersByUserAndQuestion($userId, $questionId);
        }
    }
}
