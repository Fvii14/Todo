<?php

namespace App\Listeners;

use App\Events\EventUserIsNotBeneficiary;
use App\Services\EventHandlerService;
use Illuminate\Support\Facades\Log;

class ListenerUserIsNotBeneficiary
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
     * @param EventUserIsNotBeneficiary $event
     * @return void
     */
    public function handle(EventUserIsNotBeneficiary $event ): void
    {
        $this->handler->handle($event);
    }
}
