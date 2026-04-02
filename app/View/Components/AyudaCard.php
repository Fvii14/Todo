<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AyudaCard extends Component
{
    public $ayudaSolicitada;

    public $nConvivientes;

    public $convivientes;

    public $estadoPrincipal;

    public $estadoConvivientes;

    public $sectorAyuda;

    public function __construct($ayudaSolicitada, $nConvivientes, $convivientes, $estadoPrincipal, $estadoConvivientes, $sectorAyuda)
    {
        $this->ayudaSolicitada = $ayudaSolicitada;
        $this->nConvivientes = $nConvivientes;
        $this->convivientes = $convivientes;
        $this->estadoPrincipal = $estadoPrincipal;
        $this->estadoConvivientes = $estadoConvivientes;
        $this->sectorAyuda = $sectorAyuda;
    }

    public function render()
    {
        return view('components.ayuda-card');
    }
}
