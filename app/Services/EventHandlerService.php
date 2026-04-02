<?php

namespace App\Services;

use App\Events\EventBrevo;
use App\Events\EventAmplitude;
use App\Events\EventHubspot;
use Illuminate\Support\Facades\Log;

class EventHandlerService
{
    private static $processedEvents = [];

    /**
     * Genera un identificador único para el evento basado en su contenido
     * Esto es más robusto que spl_object_hash ya que funciona incluso si el objeto se serializa/deserializa
     * 
     * @param $event
     * @return string
     */
    private function generateEventId($event): string
    {
        $eventClass = get_class($event);
        
        // Crear un identificador basado en el contenido del evento
        $data = [];
        
        // Para EventUserContracted, usar user_id y ayuda_id
        if (isset($event->user) && isset($event->user->id)) {
            $data['user_id'] = $event->user->id;
        }
        if (isset($event->ayuda) && isset($event->ayuda->id)) {
            $data['ayuda_id'] = $event->ayuda->id;
        }
        
        // Para EventContratacionCompleted, usar contratacion_id
        if (isset($event->contratacion) && isset($event->contratacion->id)) {
            $data['contratacion_id'] = $event->contratacion->id;
        }
        
        // Para otros eventos, usar propiedades comunes
        if (isset($event->data) && is_array($event->data)) {
            $data = array_merge($data, $event->data);
        }
        
        // Crear un hash único basado en la clase y los datos
        // Ordenar el array por claves para garantizar consistencia
        ksort($data);
        $identifier = $eventClass . '|' . json_encode($data);
        return md5($identifier);
    }

    /**
     * Redirige el evento a la plataforma correspondiente
     * 
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $eventId = $this->generateEventId($event);
        $eventClass = get_class($event);
        
        // Prevenir procesamiento duplicado del mismo evento
        if (isset(self::$processedEvents[$eventId])) {
            Log::warning('[EventHandlerService] Evento ya procesado, ignorando duplicado', [
                'event' => $eventClass,
                'eventId' => $eventId,
                'timestamp' => self::$processedEvents[$eventId]
            ]);
            return;
        }
        
        // Guardar timestamp para debugging
        self::$processedEvents[$eventId] = now()->toDateTimeString();
        
        Log::info('[EventHandlerService] Procesando evento', [
            'event' => $eventClass,
            'eventId' => $eventId,
            'timestamp' => self::$processedEvents[$eventId]
        ]);

        // Compatibilidad con ambos nombres: platforms (inglés) y plataformas (español)
        $platforms = $event->platforms ?? [];
        
        if (empty($platforms)) {
            Log::warning('[EventHandlerService] No se encontraron plataformas en el evento', [
                'event' => get_class($event)
            ]);
            return;
        }
        
        foreach ($platforms as $plataforma) {
            try {
                switch (strtolower($plataforma)) {
                    case 'brevo':
                        Log::info('[EventHandlerService] Disparando EventBrevo', [
                            'eventId' => $eventId
                        ]);
                        event(new EventBrevo($event));
                        break;
                    case 'amplitude':
                        event(new EventAmplitude($event));
                        break;
                    case 'hubspot':
                        Log::info('[EventHandlerService] Disparando EventHubspot', [
                            'eventId' => $eventId
                        ]);
                        event(new EventHubspot($event));
                        break;
                    default:
                        // Opcional: log o excepción si la plataforma no existe
                        break;
                }
            } catch (\Throwable $e) {
                Log::error('[EventRouter] Error al enviar ' . get_class($event) . ' a ' . $plataforma . ': ' . $e->getMessage());
            }
        }
    }
}
