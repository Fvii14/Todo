<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserNoBeneficiarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $name;

    public $ayuda;

    public $cuantia_total;

    /**
     * Constructor de la clase UserNoBeneficiarioMail.
     * Usado justo cuando al usuario le sale la lógica como no beneficiario
     *
     * @param  User  $user
     * @return void
     */
    public function __construct($user, $ayuda)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->ayuda = $ayuda;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '😢 No eres beneficiario de la ayuda',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nobeneficiario',
            with: [
                'name' => $this->name,
                'ayuda' => $this->ayuda,
            ]
        );
    }
}
