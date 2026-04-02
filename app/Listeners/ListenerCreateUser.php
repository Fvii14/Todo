<?php

namespace App\Listeners;

use App\Events\EventUserRegistered;
use App\Services\BrevoService;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\EventHandlerService;

class ListenerCreateUser
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
     * @param EventUserRegistered $event
     * @return void
     */
    public function handle(EventUserRegistered $event): void
    {
        $this->handler->handle($event);
    }
}
