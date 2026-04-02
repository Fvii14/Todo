<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserBeneficiarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $name;

    public $ayuda;

    public $cuantia_total;

    /**
     * Constructor de la clase CollectorFinished.
     * Usado justo cuando el usuario ha completado el formulario de Collector.
     * Ya sea por Bankflip o no.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct($user, $ayuda)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->ayuda = $ayuda;
        $this->cuantia_total = $ayuda->cuantia_usuario;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 ¡Enhorabuena, '.$this->name.'! Eres beneficiario de la ayuda '.$this->ayuda->nombre_ayuda,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.beneficiario',
            with: [
                'name' => $this->name,
                'ayuda' => $this->ayuda,
                'cuantia_total' => $this->cuantia_total,
            ]
        );
    }
}
