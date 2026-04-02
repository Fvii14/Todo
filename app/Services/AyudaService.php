<?php

namespace App\Services;

use App\Models\Ayuda;
use App\Models\AyudaSolicitada;

class AyudaService
{
    /**
     * Obtiene una ayuda por ID de cuestionario
     */
    public function getAyudaByQuestionnaireId(int $questionnaireId): ?Ayuda
    {
        return Ayuda::where('questionnaire_id', $questionnaireId)->first();
    }

    /**
     * Obtiene una ayuda por ID
     */
    public function getAyudaById(int $id): ?Ayuda
    {
        return Ayuda::find($id);
    }

    /**
     * Verifica si un usuario ya tiene una ayuda solicitada
     */
    public function checkIfUserHasAyudaSolicitada(int $userId, int $ayudaId): bool
    {
        return AyudaSolicitada::where('user_id', $userId)
            ->where('ayuda_id', $ayudaId)
            ->exists();
    }

    /**
     * Crea una nueva ayuda solicitada
     */
    public function createAyudaSolicitada(array $data): AyudaSolicitada
    {
        return AyudaSolicitada::create($data);
    }
}
