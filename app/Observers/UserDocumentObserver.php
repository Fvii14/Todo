<?php

namespace App\Observers;

use App\Events\EventContratacionCompleted;
use App\Models\Contratacion;
use App\Models\UserDocument;
use App\Services\EstadoContratacionService;
use Illuminate\Support\Facades\Log;

class UserDocumentObserver
{
    public function updated(UserDocument $document)
    {
        // Si se acaba de validar
        if ($document->estado === 'validado') {

            $contratacion = Contratacion::where('user_id', $document->user_id)
                ->where('ayuda_id', $document->ayuda_id)
                ->first();

            // Si está en documentación (OPx) y la documentación está completa → añadir OP1-Tramitacion
            if ($contratacion && $contratacion->estadosContratacion()->where('codigo', 'OP1-Documentacion')->exists()) {
                if ($contratacion->isCompleta()) {
                    app(EstadoContratacionService::class)->syncEstadosByCodigos(
                        $contratacion,
                        ['OP1-Tramitacion'],
                        false
                    );
                    Log::info("✅ Contratación {$contratacion->id}: añadido OP1-Tramitacion (documentación completa)");
                }

                // Verificar si la contratación está completamente lista (documentos + formularios)
                if ($contratacion->estaCompletamenteLista()) {
                    // Disparar evento de contratación completada
                    event(new EventContratacionCompleted($contratacion));

                    Log::info("✅ Evento EventContratacionCompleted disparado para contratación {$contratacion->id} - Documentos y formularios completos");
                }
            }
        }
    }

    /**
     * Enviar WhatsApp con template 378 para documento rechazado
     */
    // !! Brevo esto lo tenemos que replicar con eventos y router

    private function enviarWhatsAppDocumentoRechazado(UserDocument $userDocument)
    {
        try {
            $user = $userDocument->user;

            // Obtener teléfono del usuario
            $telefono = \App\Models\Answer::where('user_id', $user->id)
                ->where('question_id', 45) // ID de la pregunta del teléfono
                ->whereNull('conviviente_id')
                ->value('answer');

            if (! $telefono) {
                Log::warning('No se encontró teléfono para usuario en documento rechazado (Observer)', [
                    'user_id' => $user->id,
                    'user_document_id' => $userDocument->id,
                ]);

                return;
            }

            // Formatear teléfono
            $telefonoFormateado = preg_replace('/[^0-9]/', '', $telefono);
            if (! str_starts_with($telefonoFormateado, '34')) {
                $telefonoFormateado = '34'.$telefonoFormateado;
            }

            // Enviar WhatsApp con template 378
            $brevoService = app(\App\Services\BrevoService::class);
            $response = $brevoService->sendWhatsAppMessageWithParams($telefonoFormateado, 378, [
                'NOMBRE' => $user->name,
                'DOCUMENTO' => $userDocument->document->name ?? 'documento',
            ]);

            Log::info('WhatsApp enviado para documento rechazado (Observer)', [
                'user_id' => $user->id,
                'user_document_id' => $userDocument->id,
                'telefono' => $telefonoFormateado,
                'template_id' => 378,
                'documento' => $userDocument->document->name ?? 'documento',
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp para documento rechazado (Observer)', [
                'user_id' => $userDocument->user_id ?? 'unknown',
                'user_document_id' => $userDocument->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
