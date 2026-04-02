<?php

namespace App\Events;

use App\Models\Contratacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCobroRealizado
{
    use Dispatchable, SerializesModels;

    /** @var Contratacion Contratación en la que se marcó la comisión del pago como cobrada */
    public $contratacion;

    /** @var array Plataformas a las que enviar el evento (ej. hubspot) */
    public $platforms;

    /**
     * Crear una nueva instancia del evento.
     * Se dispara cuando operativa marca un pago de administración como cobrado (el cliente ya pagó la comisión).
     *
     * @param  Contratacion  $contratacion  Contratación afectada (con estados OPx ya actualizados)
     */
    public function __construct(Contratacion $contratacion, array $platforms = ['hubspot'])
    {
        $this->contratacion = $contratacion;
        $this->platforms = $platforms;
    }
}
