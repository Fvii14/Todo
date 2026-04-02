<?php

namespace App\Listeners;

use App\Events\EventBrevo;
use App\Services\BrevoService;
use Illuminate\Support\Facades\Log;

class ListenerBrevo
{
    protected $brevoServices;
    private static $processedEvents = [];
   
    public function __construct(BrevoService $brevoServices)
    {
        $this->brevoServices = $brevoServices;
    }

    
    public function handle(EventBrevo $event): void
    {
        $eventId = spl_object_hash($event);
        
        // Prevenir procesamiento duplicado del mismo evento
        if (isset(self::$processedEvents[$eventId])) {
            Log::warning('[LISTENER][Brevo] EventBrevo ya procesado, ignorando duplicado', [
                'eventId' => $eventId
            ]);
            return;
        }
        
        self::$processedEvents[$eventId] = true;
        
        $this->brevoServices->procesar($event->eventoOriginal);
    }
}
