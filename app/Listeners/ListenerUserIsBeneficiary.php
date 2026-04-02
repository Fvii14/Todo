<?php

namespace App\Listeners;

use App\Services\EventHandlerService;
use App\Events\EventUserIsBeneficiary;
use Illuminate\Support\Facades\Log;
class ListenerUserIsBeneficiary
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
     * @param EventUserIsBeneficiary $event
     * @return void
     */
    public function handle(EventUserIsBeneficiary $event): void
    {
        $this->handler->handle($event);
    }
}
