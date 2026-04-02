<?php

namespace App\Listeners;

use App\Events\EventContratacionCierreResolucion;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerContratacionCierreResolucion
{
    protected $eventHandlerService;

    public function __construct(EventHandlerService $eventHandlerService)
    {
        $this->eventHandlerService = $eventHandlerService;
    }

    public function handle(EventContratacionCierreResolucion $event): void
    {
        Log::info('[ListenerContratacionCierreResolucion] Procesando evento', [
            'contratacion_id' => $event->contratacion->id,
        ]);

        $this->eventHandlerService->handle($event, ['hubspot']);
    }
}

