<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BonoCulturalJovenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $step;

    public $nombrePila;

    public function __construct($user, $step, $nombrePila)
    {
        $this->user = $user;
        $this->step = $step;
        $this->nombrePila = $nombrePila;
    }

    /**
     * Asunto del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->step) {
                1 => '🎟️ ¡Consigue tu Bono Cultural de 400€ con TTF!',
                2 => '⏰ ¡No lo dejes pasar, los 400 € te esperan!',
                3 => '📢 ¡Últimos días para pedir el Bono Cultural!',
                default => '¡Aprovecha tu Bono Cultural Joven!',
            }
        );
    }

    /**
     * Vista y datos del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bono_cultural_joven_2025',
            with: [
                'user' => $this->user,
                'step' => $this->step,
                'nombrePila' => $this->nombrePila,
            ]
        );
    }

    /**
     * Archivos adjuntos (opcional).
     */
    public function attachments(): array
    {
        return [];
    }
}
