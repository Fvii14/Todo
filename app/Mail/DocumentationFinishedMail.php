<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentationFinishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $ayuda;

    public function __construct($user, $ayuda)
    {
        $this->user = $user;
        $this->ayuda = $ayuda;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu ayuda está en camino!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.documentationfinish',
            with: [
                'user' => $this->user,
                'ayuda' => $this->ayuda,
            ]
        );
    }
}
