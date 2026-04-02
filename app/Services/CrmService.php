<?php

namespace App\Services;

use App\Models\CrmStateHistory;
use App\Models\UserAyuda;

class CrmService
{
    /**
     * Actualiza o crea un registro de UserAyuda
     */
    public function updateOrCreateUserAyuda(array $attributes, array $values): UserAyuda
    {
        return UserAyuda::updateOrCreate($attributes, $values);
    }

    /**
     * Crea un registro en el historial de estados de CRM
     */
    public function createCrmStateHistory(array $data): CrmStateHistory
    {
        return CrmStateHistory::create($data);
    }

    /**
     * Elimina registros obsoletos de UserAyuda con ayuda_id null
     */
    public function deleteObsoleteUserAyudas(int $userId): void
    {
        UserAyuda::where('user_id', $userId)
            ->whereNull('ayuda_id')
            ->delete();
    }

    /**
     * Marca a un usuario como beneficiario
     */
    public function markUserAsBeneficiary(int $userId, int $ayudaId): void
    {
        $this->updateOrCreateUserAyuda(
            ['user_id' => $userId, 'ayuda_id' => $ayudaId],
            [
                'estado_comercial' => 'caliente',
                'pipeline' => 'Beneficiario',
                'fecha_formulario' => now(),
                'updated_at' => now(),
            ]
        );

        // Crear historial: Cualificado → Beneficiario
        $this->createCrmStateHistory([
            'user_id' => $userId,
            'ayuda_id' => $ayudaId,
            'from_stage' => 'Cualificado',
            'to_stage' => 'Beneficiario',
            'from_temp' => 'tibio',
            'to_temp' => 'caliente',
            'event' => 'beneficiary_confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear historial: Beneficiario → No contrata
        $this->createCrmStateHistory([
            'user_id' => $userId,
            'ayuda_id' => $ayudaId,
            'from_stage' => 'Beneficiario',
            'to_stage' => 'No contrata',
            'from_temp' => 'caliente',
            'to_temp' => 'caliente',
            'event' => 'no_contract_created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Marca a un usuario como no beneficiario
     */
    public function markUserAsNonBeneficiary(int $userId, int $ayudaId): void
    {
        $this->updateOrCreateUserAyuda(
            ['user_id' => $userId, 'ayuda_id' => $ayudaId],
            [
                'estado_comercial' => 'frio',
                'pipeline' => 'No beneficiario',
                'fecha_formulario' => now(),
                'updated_at' => now(),
            ]
        );

        $this->createCrmStateHistory([
            'user_id' => $userId,
            'ayuda_id' => $ayudaId,
            'from_stage' => 'Cualificado',
            'to_stage' => 'No beneficiario',
            'from_temp' => 'tibio',
            'to_temp' => 'frio',
            'event' => 'non_beneficiary',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Marca a un usuario como test completado (collector)
     */
    public function markUserAsTestCompleted(int $userId): void
    {
        // Crear historial de CRM
        $this->createCrmStateHistory([
            'user_id' => $userId,
            'ayuda_id' => null,
            'from_stage' => 'Captado',
            'to_stage' => 'Test hecho',
            'from_temp' => 'frio',
            'to_temp' => 'tibio',
            'event' => 'generic_form_completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Actualizar UserAyuda
        $this->updateOrCreateUserAyuda(
            ['user_id' => $userId, 'ayuda_id' => null],
            [
                'estado_comercial' => 'tibio',
                'pipeline' => 'Test hecho',
                'fecha_formulario' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
