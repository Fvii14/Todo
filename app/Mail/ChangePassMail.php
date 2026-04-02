<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangePassMail extends Mailable
{
    use Queueable, SerializesModels;

    // Propiedades para almacenar los datos que se van a pasar al correo
    public $name;

    public $resetLink;

    /**
     * Crear una nueva instancia del mensaje.
     *
     * @param  string  $name  El nombre del usuario
     * @param  string  $resetLink  El enlace para restablecer la contraseña
     */
    public function __construct($name, $resetLink)
    {
        $this->name = $name;
        $this->resetLink = $resetLink;
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecimiento de Contraseña',
        );
    }

    /**
     * Obtener la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.plantilla-changepass',
            with: [
                'name' => $this->name,
                'resetLink' => $this->resetLink,
            ]
        );
    }

    /**
     * Obtener los archivos adjuntos para el mensaje.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
