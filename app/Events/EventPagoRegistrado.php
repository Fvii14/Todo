<?php

namespace App\Events;

use App\Models\Contratacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventPagoRegistrado
{
    use Dispatchable, SerializesModels;

    /** @var Contratacion */
    public $contratacion;

    /** @var array Plataformas a las que enviar el evento (ej. hubspot) */
    public $platforms;

    /**
     * Crear una nueva instancia del evento.
     *
     * @param  Contratacion  $contratacion  Contratación a la que se le registró el pago
     */
    public function __construct(Contratacion $contratacion, array $platforms = ['hubspot'])
    {
        $this->contratacion = $contratacion;
        $this->platforms = $platforms;
    }
}
