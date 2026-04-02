<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Contratacion;

/**
 * Evento cuando cambia el estado de una contratación
 * Útil para rastrear el progreso en el funnel de HubSpot
 * 
 * @param Contratacion $contratacion
 * @param string $estadoAnterior
 * @param string $estadoNuevo
 * @param string|null $faseAnterior
 * @param string|null $faseNueva
 * @param array $platforms
 */
class EventContratacionStatusChanged
{
    use Dispatchable, SerializesModels;

    public $contratacion;
    public $estadoAnterior;
    public $estadoNuevo;
    public $faseAnterior;
    public $faseNueva;
    public $platforms;

    public function __construct(
        Contratacion $contratacion,
        string $estadoAnterior,
        string $estadoNuevo,
        ?string $faseAnterior = null,
        ?string $faseNueva = null,
        array $platforms = ['hubspot']
    ) {
        $this->contratacion = $contratacion;
        $this->estadoAnterior = $estadoAnterior;
        $this->estadoNuevo = $estadoNuevo;
        $this->faseAnterior = $faseAnterior;
        $this->faseNueva = $faseNueva;
        $this->platforms = $platforms;
    }
}

