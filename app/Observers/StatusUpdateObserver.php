<?php

namespace App\Observers;

use App\Mail\StatusUpdateMail;
use App\Models\Contratacion;
use Illuminate\Support\Facades\Mail;

class StatusUpdateObserver
{
    /**
     * Handle the Contratacion "created" event.
     */
    public function created(Contratacion $contratacion): void
    {
        //
    }

    /**
     * Handle the Contratacion "updated" event.
     */
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
