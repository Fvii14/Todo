<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;

    public $step;

    /**
     * Crear una nueva instancia del mensaje.
     *
     * @param  string  $name  El nombre del usuario
     * @param  string  $step  El paso del mail
     */
    public function __construct($name, $step)
    {
        $this->name = $name; // Asignamos el nombre al atributo
        $this->step = $step;
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        // 1 -> Al momento de crear el perfil
        // 2 -> A las 24 horas de crear el perfil
        // 3 -> A las 72 horas de crear el perfil
        // 4 -> Tras una semana desde que crea el perfil
        // Todo esto si no ha hecho la conexión con Cl@ve
        return new Envelope(
            subject: match ($this->step) {
                1 => '¡'.$this->name.' Bienvenido/a a Tu Trámite Fácil!',
                2 => '⏰ ¡No dejes pasar tu oportunidad, '.$this->name.'!',
                3 => '¿Todavía no has descubierto tus ayudas, '.$this->name.'?',
                4 => '🚨 Última oportunidad para descubrir tus ayudas',
            }
        );
    }

    /**
     * Obtener la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.plantilla-welcome', // Esta es la vista de la plantilla que has creado
            with: [
                'name' => $this->name, // Pasamos el nombre a la vista
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
