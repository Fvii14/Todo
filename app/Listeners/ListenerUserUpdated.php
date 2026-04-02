<?php

namespace App\Listeners;

use App\Events\EventUserUpdated;
use App\Services\BrevoService;
use Illuminate\Support\Facades\Log;
use App\Services\EventHandlerService;

class ListenerUserUpdated
{
    protected $handler;

    /**
     * Create a new listener instance.
     * 
     * @param EventHandlerService $handler
     * @return void
     */
    public function __construct(EventHandlerService $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Handle the event.
     * 
     * @param EventUserUpdated $event
     * @return void
     */
    public function handle(EventUserUpdated $event): void
    {

        $this->handler->handle($event);
    }
}
