<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContratacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;

    public $ayuda;

    public $step;

    public function __construct($name, $ayuda, $step)
    {
        $this->name = $name;
        $this->ayuda = $ayuda;
        $this->step = $step;
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        // TODO: Pendiente
        // 1 -> Not sure yet
        // 2 -> Not sure yet
        // 3 -> Not sure yet
        // 4 -> Not sure yet
        // 5 -> Not sure yet
        return new Envelope(
            subject: match ($this->step) {
                1 => '⚠️ Documentación pendiente para tu ayuda',
                2 => '¿Crees que podrías hacerlo mejor solo? Te contamos la realidad...',
                3 => '¿Qué dicen otros como tú sobre tramitar con nosotros?',
                4 => 'Olvídate del papeleo: te contamos por qué cada minuto cuenta ⌛',
                5 => '⌛ Tu ayuda de '.$this->ayuda->cuantia_usuario.'€ va a caducar pronto...',
            }
        );
    }

    /**
     * Obtener la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ayudacontratada',
            with: [
                'name' => $this->name,
                'ayuda' => $this->ayuda,
                'step' => $this->step,
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
