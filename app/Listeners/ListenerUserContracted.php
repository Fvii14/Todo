<?php

namespace App\Listeners;

use App\Events\EventUserContracted;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerUserContracted
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
     * @param EventUserContracted $event
     * @return void
     */
    public function handle(EventUserContracted $event): void
    {
        
        $this->handler->handle($event);
    }
}
