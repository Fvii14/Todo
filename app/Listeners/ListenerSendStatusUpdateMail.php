<?php

namespace App\Listeners;

use App\Events\StatusUpdated;
use App\Mail\StatusUpdateMail;
use Illuminate\Support\Facades\Mail;

class ListenerSendStatusUpdateMail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    // public function handle(StatusUpdated $event): void
    // {
    //     Mail::to($event->contratacion->user->email)->send(
    //         new StatusUpdateMail($event->contratacion, $event->nuevo_estado)
    //     );

    // }
}
