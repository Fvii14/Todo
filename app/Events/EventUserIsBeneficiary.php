<?php

namespace App\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Ayuda;

class EventUserIsBeneficiary
{
    use Dispatchable, SerializesModels;

    public $user;
    public $ayuda;
    public $platforms;

    /**
     * Create a new event instance.
     * 
     * @param User $user
     * @param Ayuda $ayuda
     * @param array $platforms Se pueden añadir más plataformas aquí
     */
    public function __construct(User $user, Ayuda $ayuda, array $platforms = ['hubspot'])
    {
        $this->user = $user;
        $this->ayuda = $ayuda;
        $this->platforms = $platforms;
    }

}
