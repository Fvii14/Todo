<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\UserDocument;
use App\Models\Contratacion;

/**
 * Evento cuando un documento es rechazado
 * 
 * @param User $user
 * @param UserDocument $userDocument
 * @param Contratacion|null $contratacion
 * @param string|null $notaRechazo
 * @param array $platforms
 */
class EventDocumentRejected
{
    use Dispatchable, SerializesModels;

    public $user;
    public $userDocument;
    public $contratacion;
    public $notaRechazo;
    public $platforms;

    public function __construct(User $user, UserDocument $userDocument, ?Contratacion $contratacion = null, ?string $notaRechazo = null, array $platforms = ['hubspot'])
    {
        $this->user = $user;
        $this->userDocument = $userDocument;
        $this->contratacion = $contratacion;
        $this->notaRechazo = $notaRechazo;
        $this->platforms = $platforms;
    }
}

