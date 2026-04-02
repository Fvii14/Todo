<?php

// app/Mail/EstadoTramitandoMail.php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RecordatorioReferidoMail extends Mailable
{
    public $user;

    public $step;

    public function __construct(User $user, int $step)
    {
        $this->user = $user;
        $this->step = $step;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->step) {
                1 => '💸 ¿Quieres ganar 5€ con tu código?',
                2 => '📢 ¡Tu código sigue activo!',
                3 => '🎯 Aún estás a tiempo de ganar con tu código',
                4 => '⏳ Última oportunidad para ganar 5€ por referido',
                default => 'Gana 5€ por invitar a tus amigos',
            }
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio_referido',
            with: [
                'user' => $this->user,
                'step' => $this->step,
                'codigo' => $this->user->ref_code,
                'nombre' => $this->user->nombrePila() ?? '',
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
