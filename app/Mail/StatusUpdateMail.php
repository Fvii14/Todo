<?php

namespace App\Mail;

use App\Models\Contratacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contratacion;

    public $nuevo_estado;

    /**
     * Crear una nueva instancia del mensaje.
     *
     * @param  string  $nuevo_estado
     */
    public function __construct(Contratacion $contratacion, $nuevo_estado)
    {
        $this->contratacion = $contratacion;
        $this->nuevo_estado = $nuevo_estado;
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización en tu solicitud de ayuda'
        );
    }

    /**
     * Obtener el contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.plantilla-update',
            with: [
                'name' => $this->contratacion->user->name,
                'nombre_ayuda' => $this->contratacion->ayuda->nombre_ayuda,
                'nuevo_estado' => $this->nuevo_estado,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
