<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BonoCulturalDocumentacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $step;

    public $codigo;

    public $nombrePila;

    public function __construct($user, $step)
    {
        $this->user = $user;
        $this->step = $step;
        $this->codigo = $user->ref_code ?? '';
        $this->nombrePila = $user->nombrePila() ?? '';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->step) {
                1 => '⚠️ Documentación pendiente para tu ayuda',
                2 => '📄 No te olvides de subir tus documentos',
                3 => '🚨 Último paso para recibir tu ayuda',
                4 => '📢 ¡Estás dentro! Ahora toca mover ficha',
                default => 'Falta documentación',
            }
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bono_cultural_documentacion',
            with: [
                'user' => $this->user,
                'step' => $this->step,
                'codigo' => $this->codigo,
                'nombrePila' => $this->nombrePila,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
