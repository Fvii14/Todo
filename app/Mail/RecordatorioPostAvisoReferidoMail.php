<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioPostAvisoReferidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $nombrePila;

    public string $codigo;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->nombrePila = $user->nombrePila() ?? '';
        $this->codigo = $user->ref_code ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📣 ¡Tu código sigue activo y puedes ganar más dinero!'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio_post_aviso_referido',
            with: [
                'nombrePila' => $this->nombrePila,
                'codigo' => $this->codigo,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
