<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

/**
 * Evento para actualizar un usuario
 * 
 * @param User $user
 * @param array $data
 * @param array $platforms Se pueden añadir más plataformas aquí
 */
class EventUserUpdated
{
    use Dispatchable, SerializesModels;

    public $data;
    public $platforms;
    public $user;
    public function __construct(User $user, array $data, array $platforms = ["hubspot"])
    {
        $this->user = $user;
        $this->data = $data;
        $this->platforms = $platforms;
    }
}
