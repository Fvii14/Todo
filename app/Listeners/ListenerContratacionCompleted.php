<?php

namespace App\Listeners;

use App\Events\EventContratacionCompleted;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerContratacionCompleted
{
    protected $eventHandler;

    public function __construct(EventHandlerService $eventHandler)
    {
        $this->eventHandler = $eventHandler;
    }

    public function handle(EventContratacionCompleted $event): void
    {
        Log::info('[ListenerContratacionCompleted] Procesando EventContratacionCompleted', [
            'contratacion_id' => $event->contratacion->id ?? null,
        ]);

        $this->eventHandler->handle($event);
    }
}

