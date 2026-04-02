<?php

namespace App\Observers;

use App\Mail\StatusUpdateMail;
use App\Models\Contratacion;
use App\Models\MailTracking;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ContratacionObserver
{
    public function created(Contratacion $contratacion)
    {
        $user = $contratacion->user;

        if (
            $contratacion->ayuda?->slug === 'bono_cultural_joven_2025' &&
            $user->ref_by &&
            ! MailTracking::hasBeenSentTo($user->ref_by, \App\Mail\AvisoContratacionReferidoMail::class)
        ) {

            $referrer = \App\Models\User::find($user->ref_by);

            if ($referrer) {
                $referrerNombrePila = $referrer->nombrePila() ?? '';
                Mail::to($referrer->email)->send(new \App\Mail\AvisoContratacionReferidoMail($referrerNombrePila, $referrer, $user));
                MailTracking::track($referrer, \App\Mail\AvisoContratacionReferidoMail::class);
            }
        }
    }

    public function updated(Contratacion $contratacion): void
    {
        if ($contratacion->isDirty('estado')) {
            // Cargar relaciones necesarias
            $contratacion->load('user', 'ayuda');

            // Enviar correo de actualización de estado
            // Mail::to($contratacion->user->email)->send(
            //     new StatusUpdateMail($contratacion, $contratacion->estado)
            // );
        }
    }
}
