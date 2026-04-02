<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\HubspotWebhookService;

class ProcessHubspotWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos antes de marcar como fallido
     */
    public $tries = 3;

    /**
     * Tiempo máximo de ejecución en segundos
     */
    public $timeout = 60;

    /**
     * Tiempo de espera entre reintentos (segundos)
     */
    public $backoff = [10, 30, 60];

    /**
     * Payload del webhook
     */
    public $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        // Usar cola dedicada para webhooks
        $this->onQueue('hubspot-webhooks');
    }

    /**
     * Execute the job.
     */
    public function handle(HubspotWebhookService $service): void
    {
        try {
            Log::info('[ProcessHubspotWebhook] Procesando webhook', [
                'subscription_type' => $this->payload['subscriptionType'] ?? null,
                'event_id' => $this->payload['eventId'] ?? null,
            ]);

            // Procesar el webhook según su tipo
            $service->process($this->payload);

            Log::info('[ProcessHubspotWebhook] Webhook procesado correctamente', [
                'event_id' => $this->payload['eventId'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('[ProcessHubspotWebhook] Error al procesar webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $this->payload,
            ]);

            // Re-lanzar para que Laravel lo reintente
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[ProcessHubspotWebhook] Job fallido después de todos los reintentos', [
            'error' => $exception->getMessage(),
            'payload' => $this->payload,
        ]);

        // Aquí podrías:
        // - Enviar notificación a Slack/Email
        // - Guardar en una tabla de webhooks fallidos
        // - Re-enviar a una cola de "dead letter"
    }
}

