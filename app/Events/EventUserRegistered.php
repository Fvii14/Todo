<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EventUserRegistered
{
    use Dispatchable, SerializesModels;

    public $data;
    public $platforms;
    public $user;
    /**
     * Create a new event instance.
     * 
     * @param User $user
     * @param array $platforms Se pueden añadir más plataformas aquí
     */
    public function __construct(User $user, array $platforms = ["hubspot"])
    {
        // Extraer los datos necesarios del usuario
        $this->user = $user;
        $this->data = [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->nombrePila(),
            'lastname' => trim(collect([$user->apellido1 ?? null, $user->apellido2 ?? null])->filter()->implode(' ')) ?: null,
        ];
        $this->platforms = $platforms;
    }
}





