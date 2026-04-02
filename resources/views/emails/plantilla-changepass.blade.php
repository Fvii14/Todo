@extends('emails.EmailLayout')

@section('content')
    <h2>Hola, {{ $name }}</h2>

    <p>
        Hemos recibido una solicitud para cambiar la contraseña de tu cuenta. Si fuiste tú, puedes usar el siguiente enlace para restablecer tu contraseña:
    </p>

    <a href="{{ $resetLink }}"
       style="display: inline-block; margin-top: 16px; background-color: #54debd; color: #FFFFFF; padding: 10px 16px; border-radius: 6px; text-decoration: none;">
        Restablecer contraseña
    </a>

    <p style="margin-top: 20px;">
        Si no solicitaste un cambio de contraseña, por favor ignora este mensaje.
    </p>

    <p style="font-size: 14px; color: #777;">
        Si tienes alguna duda, no dudes en contactarnos.
    </p>
@endsection
