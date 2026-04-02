<?php

namespace App\Listeners;

use App\Events\EventHubspot;
use App\Services\HubspotService;
use Illuminate\Support\Facades\Log;

class ListenerHubspot
{
    protected $hubspotService;
    private static $processedEvents = [];
   
    public function __construct(HubspotService $hubspotService)
    {
        $this->hubspotService = $hubspotService;
    }

    
    public function handle(EventHubspot $event): void
    {
        $eventId = spl_object_hash($event);
        
        // Prevenir procesamiento duplicado del mismo evento
        if (isset(self::$processedEvents[$eventId])) {
            Log::warning('[LISTENER][Hubspot] EventHubspot ya procesado, ignorando duplicado', [
                'eventId' => $eventId
            ]);
            return;
        }
        
        self::$processedEvents[$eventId] = true;
        
        $this->hubspotService->procesar($event->eventoOriginal);
    }
}

