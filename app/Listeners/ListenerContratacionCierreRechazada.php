<?php

namespace App\Listeners;

use App\Events\EventContratacionCierreRechazada;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerContratacionCierreRechazada
{
    protected $eventHandlerService;

    public function __construct(EventHandlerService $eventHandlerService)
    {
        $this->eventHandlerService = $eventHandlerService;
    }

    public function handle(EventContratacionCierreRechazada $event): void
    {
        Log::info('[ListenerContratacionCierreRechazada] Procesando evento', [
            'contratacion_id' => $event->contratacion->id,
        ]);

        $this->eventHandlerService->handle($event, ['hubspot']);
    }
}

