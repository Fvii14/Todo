<?php

namespace App\Http\Controllers;

use App\Services\GcsSignedUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadMagnetController extends Controller
{
    public function download(Request $request, GcsSignedUrlService $gcs)
    {
        // Parámetros opcionales que puedes pasar desde la newsletter (Brevo)
        $email = (string) $request->query('email', '');
        $cid = (string) $request->query('cid', ''); // id del contacto si lo tienes
        $utm = [
            'source' => $request->query('utm_source'),
            'medium' => $request->query('utm_medium'),
            'campaign' => $request->query('utm_campaign'),
        ];

        // Aquí podrías validar que existe el contacto y guardar/actualizar el lead en BD

        Log::info('[LeadMagnet] click newsletter', [
            'email' => $email,
            'cid' => $cid,
            'utm' => $utm,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'ts' => now()->toIso8601String(),
        ]);

        // Ruta real del PDF en tu bucket GCS (privado)
        $objectPath = 'Documentos_descarga/Leadmagnet general-TTF-.pdf';

        // Genera URL firmada temporal (60 min) y fuerza descarga con nombre amigable
        $signedUrl = $gcs->makeDownloadUrl(
            objectPath: $objectPath,
            minutes: 60,
            downloadAs: 'Guia-Organiza-tu-economia.pdf'
        );

        // Redirige al enlace firmado (descarga inmediata)
        return redirect()->away($signedUrl);
    }
}
