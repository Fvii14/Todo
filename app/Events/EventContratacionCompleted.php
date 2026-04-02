<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Contratacion;

/**
 * Evento cuando una contratación se completa exitosamente
 * (estado: cierre, fase: resolucion)
 * 
 * @param Contratacion $contratacion
 * @param array $platforms
 */
class EventContratacionCompleted
{
    use Dispatchable, SerializesModels;

    public $contratacion;
    public $platforms;

    public function __construct(Contratacion $contratacion, array $platforms = ['hubspot'])
    {
        $this->contratacion = $contratacion;
        $this->platforms = $platforms;
    }
}

