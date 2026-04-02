<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\UserDocument;
use App\Models\Contratacion;

/**
 * Evento cuando un documento es validado
 * 
 * @param User $user
 * @param UserDocument $userDocument
 * @param Contratacion|null $contratacion
 * @param array $platforms
 */
class EventDocumentValidated
{
    use Dispatchable, SerializesModels;

    public $user;
    public $userDocument;
    public $contratacion;
    public $platforms;

    public function __construct(User $user, UserDocument $userDocument, ?Contratacion $contratacion = null, array $platforms = ['hubspot'])
    {
        $this->user = $user;
        $this->userDocument = $userDocument;
        $this->contratacion = $contratacion;
        $this->platforms = $platforms;
    }
}

