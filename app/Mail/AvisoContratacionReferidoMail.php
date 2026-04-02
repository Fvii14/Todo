<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AvisoContratacionReferidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $referrer;

    public string $referrerNombre;

    public function __construct(string $referrerNombre, User $referrer, User $referido)
    {
        $this->referrerNombre = $referrerNombre;
        $this->referrer = $referrer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 ¡Has ganado 5€ por invitar a un amigo!'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aviso_contratacion_referido',
            with: [
                'referrer' => $this->referrer,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
