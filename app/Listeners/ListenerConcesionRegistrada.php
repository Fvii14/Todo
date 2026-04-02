<?php

namespace App\Listeners;

use App\Events\EventConcesionRegistrada;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerConcesionRegistrada
{
    protected $eventHandlerService;

    public function __construct(EventHandlerService $eventHandlerService)
    {
        $this->eventHandlerService = $eventHandlerService;
    }

    public function handle(EventConcesionRegistrada $event): void
    {
        Log::info('[ListenerConcesionRegistrada] Procesando evento', [
            'contratacion_id' => $event->contratacion->id,
        ]);

        $this->eventHandlerService->handle($event);
    }
}
