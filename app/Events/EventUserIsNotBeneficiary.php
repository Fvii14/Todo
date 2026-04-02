<?php

namespace App\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Ayuda;

class EventUserIsNotBeneficiary
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $ayuda;
    public $platforms;
    public $razones_no_cumple;

    /**
     * Create a new event instance.
     * 
     * @param User $user
     * @param Ayuda $ayuda
     * @param array $platforms Se pueden añadir más plataformas aquí
     * @param array $razones_no_cumple Razones de no cumplimiento
     */
    public function __construct(User $user, Ayuda $ayuda, array $razones_no_cumple, array $platforms = ['hubspot'])
    {
        $this->user = $user;
        $this->ayuda = $ayuda;
        $this->platforms = $platforms;
        $this->razones_no_cumple = $razones_no_cumple;
    }

    
}
