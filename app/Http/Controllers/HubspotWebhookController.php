<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessHubspotWebhook;
use Illuminate\Support\Facades\Validator;

class HubspotWebhookController extends Controller
{
    /**
     * Endpoint para recibir webhooks de HubSpot
     * Valida, encola y responde rápidamente (< 2s)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Validación rápida del payload
        $validator = Validator::make($request->all(), [
            'subscriptionId' => 'required|integer',
            'portalId' => 'required|integer',
            'occurredAt' => 'required|integer',
            'subscriptionType' => 'required|string',
            'eventId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            Log::warning('[HubspotWebhook] Validación fallida', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid payload'
            ], 400);
        }

        // 2. Verificar firma del webhook (opcional pero recomendado)
        if (!$this->verifySignature($request)) {
            Log::warning('[HubspotWebhook] Firma inválida', [
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 401);
        }

        // 3. Encolar el job para procesamiento asíncrono
        try {
            ProcessHubspotWebhook::dispatch($request->all())
                ->onQueue('hubspot-webhooks'); // Cola dedicada para webhooks
            
            Log::info('[HubspotWebhook] Webhook encolado correctamente', [
                'subscription_type' => $request->input('subscriptionType'),
                'event_id' => $request->input('eventId'),
            ]);

            // 4. Responder inmediatamente con 200 OK
            return response()->json([
                'success' => true,
                'message' => 'Webhook received and queued'
            ], 200);

        } catch (\Exception $e) {
            Log::error('[HubspotWebhook] Error al encolar webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Aún así respondemos 200 para evitar reintentos de HubSpot
            // El error se manejará en el job con retry
            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook'
            ], 200);
        }
    }

    /**
     * Verifica la firma del webhook de HubSpot
     * HubSpot envía un header X-HubSpot-Signature-v3
     * 
     * @param Request $request
     * @return bool
     */
    private function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-HubSpot-Signature-v3');
        $secret = config('services.hubspot.webhook_secret');

        // Si no hay secret configurado, saltar verificación (no recomendado en producción)
        if (empty($secret)) {
            Log::warning('[HubspotWebhook] Webhook secret no configurado, saltando verificación');
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        // HubSpot usa HMAC SHA256
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, $expectedSignature);
    }

    /**
     * Endpoint de health check para HubSpot
     * HubSpot puede verificar que el endpoint está activo
     * 
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'hubspot-webhook',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

