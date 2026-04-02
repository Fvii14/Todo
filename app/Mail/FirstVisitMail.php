<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirstVisitMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $name;

    public $step;

    public $ayudasFiltradas;

    /**
     * Constructor de la clase FirstVisitMail.
     * Usado justo cuando el usuario visita la plataforma por primera vez.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct($user, $step, $ayudasFiltradas)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->step = $step;
        $this->ayudasFiltradas = $ayudasFiltradas;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // 1 -> Al momento de terminar el collector y ver todas las ayudas que te pertenecen
        // 2 -> A las 24 horas de terminar el collector
        // 3 -> A las 48 horas de terminar el collector
        // 4 -> A las 72 horas de terminar el collector
        return new Envelope(
            subject: match ($this->step) {
                1 => '🎯 '.$this->name.', estas son las ayudas que te pertenecen 🤑',
                2 => '⌛'.$this->name.', no dejes tus ayudas en el olvido',
                3 => '🚀 Solo faltan unos clics para tus ayudas',
                4 => '🚨 Última oportunidad para solicitar tus ayudas',
            },
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.firstvisit',
            with: [
                'name' => $this->name,
                'step' => $this->step,
            ]
        );
    }
}
