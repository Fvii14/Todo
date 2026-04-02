<?php

namespace App\Listeners;

use App\Events\EventCobroRealizado;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerCobroRealizado
{
    protected $eventHandlerService;

    public function __construct(EventHandlerService $eventHandlerService)
    {
        $this->eventHandlerService = $eventHandlerService;
    }

    public function handle(EventCobroRealizado $event): void
    {
        Log::info('[ListenerCobroRealizado] Procesando evento', [
            'contratacion_id' => $event->contratacion->id,
        ]);

        $this->eventHandlerService->handle($event);
    }
}
