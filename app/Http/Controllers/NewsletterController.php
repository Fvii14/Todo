<?php

namespace App\Http\Controllers;

use App\Services\BrevoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function show()
    {
        return view('newsletter.register');
    }

    public function store(Request $request, BrevoService $brevo)
    {
        // Honeypot
        if ($request->filled('website')) {
            // Responder OK silencioso para bots
            return back()->with('status', '¡Gracias! Revisa tu bandeja de entrada.');
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Debes aceptar la política de privacidad para continuar.',
        ]);

        $email = strtolower(trim($data['email']));
        $first = $data['first_name'] ?? null;
        $last = $data['last_name'] ?? null;

        // ID de lista de newsletter (ajústalo o usa config)
        $listId = (int) (config('services.brevo.lists.newsletter') ?? 174);

        try {
            // Para leads de newsletter solemos marcar REGISTRADO_EN_APP = false
            // pero tu upsertNewsletterContact fija a true. Si quieres "false" para newsletter,
            // crea un método upsertNewsletterLead() o añade un flag opcional en upsertNewsletterContact.
            // Aquí usamos el que ya tienes y añadimos un atributo SOURCE, si lo aceptas en Brevo.
            $res = $brevo->upsertNewsletterContact([
                'email' => $email,
                'FIRSTNAME' => $first,
                'LASTNAME' => $last,
                'SOURCE' => 'RRSS',
            ], $listId, false);

            if (empty($res['ok'])) {
                Log::warning('[BREVO] Newsletter signup fallo', ['email' => $email, 'res' => $res]);

                return back()->withErrors(['email' => 'No pudimos procesar tu suscripción ahora. Inténtalo más tarde.']);
            }

            return back()->with('status', '¡Gracias! Te hemos suscrito a la newsletter.');
        } catch (\Throwable $e) {
            Log::error('[BREVO] Newsletter signup exception', [
                'email' => $email,
                'ex' => $e->getMessage(),
            ]);

            // Caso típico: 401 por IP no autorizada en Brevo
            if (str_contains($e->getMessage(), 'unauthorized') || str_contains($e->getMessage(), 'unrecognised IP')) {
                return back()->withErrors(['email' => 'Servicio temporalmente no disponible. Inténtalo en unos minutos.']);
            }

            return back()->withErrors(['email' => 'Ha ocurrido un error inesperado.']);
        }
    }
}
