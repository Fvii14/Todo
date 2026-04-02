<?php

namespace App\Listeners;

use App\Events\EventPagoRegistrado;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerPagoRegistrado
{
    protected $eventHandlerService;

    public function __construct(EventHandlerService $eventHandlerService)
    {
        $this->eventHandlerService = $eventHandlerService;
    }

    public function handle(EventPagoRegistrado $event): void
    {
        Log::info('[ListenerPagoRegistrado] Procesando evento', [
            'contratacion_id' => $event->contratacion->id,
        ]);

        $this->eventHandlerService->handle($event);
    }
}
