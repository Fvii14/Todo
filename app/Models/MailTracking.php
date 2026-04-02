<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTracking extends Model
{
    protected $table = 'mail_tracking';

    protected $fillable = [
        'user_id',
        'mail_class',
        'sent_at',
        'step',
        'ayuda_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function track(User|int $user, string $mailClass, $step = null, ?int $ayudaId = null): self
    {
        $userId = $user instanceof User ? $user->id : $user;

        return self::create([
            'user_id' => $userId,
            'mail_class' => $mailClass,
            'ayuda_id' => $ayudaId,
            'step' => $step,
            'sent_at' => now(),
        ]);
    }

    // Gracias a las próximas 2 funciones, podemos obtener un texto sencillo de qué correos se mandan
    // Usando `$tracking->description`
    public function getDescriptionAttribute(): string
    {
        return self::classDescriptions()[$this->mail_class] ?? 'Correo desconocido';
    }

    public static function classDescriptions(): array
    {
        return [
            \App\Mail\WelcomeMail::class => 'Correo de bienvenida',
            \App\Mail\ChangePassMail::class => 'Cambio de contraseña',
            \App\Mail\ContratacionMail::class => 'Confirmación de contratación',
            \App\Mail\FirstVisitMail::class => 'Recordatorio de primera visita',
            \App\Mail\UserBeneficiarioMail::class => 'Notificación para usuario beneficiario',
            \App\Mail\UserNoBeneficiarioMail::class => 'Notificación para usuario no beneficiario',
            \App\Mail\AvisoContratacionReferidoMail::class => 'Aviso: referido ha contratado',

        ];
    }

    public static function hasBeenSentTo(User|int $user, string $mailClass): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return self::where('user_id', $userId)
            ->where('mail_class', $mailClass)
            ->exists();
    }
}
