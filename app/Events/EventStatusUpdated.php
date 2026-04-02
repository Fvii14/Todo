<?php

namespace App\Events;

use App\Models\Contratacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $contratacion;

    public $nuevo_estado;

    /**
     * Create a new event instance.
     * 
     * @param Contratacion $contratacion
     * @param string $nuevo_estado
     * @return void
     */
    public function __construct(Contratacion $contratacion, $nuevo_estado)
    {
        $this->contratacion = $contratacion;
        $this->nuevo_estado = $nuevo_estado;
    }
}
