<?php

namespace App\Events;

use App\Models\Contratacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventContratacionCierreRechazada
{
    use Dispatchable, SerializesModels;

    public $contratacion;

    /**
     * Create a new event instance.
     * 
     * @param Contratacion $contratacion
     * @return void
     */
    public function __construct(Contratacion $contratacion)
    {
        $this->contratacion = $contratacion;
    }
}

