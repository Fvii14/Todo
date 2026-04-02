@extends('emails.EmailLayout')

@section('title', '💸 ¿Te olvidaste de tu código? ¡Aún puedes ganar 5€!')

@section('content')

    <!-- Previsualización oculta -->
    <span style="display: none; color: transparent; visibility: hidden; height: 0; opacity: 0;">
        Tu código sigue activo. Comparte y gana 5€ por cada amigo 🤑
    </span>

    <p>Hola {{ $nombrePila }} 👋</p>

    <p>Tu código ya te ha hecho ganar dinero…</p>

    <p><strong>¿Y si sigues?</strong></p>

    <p>Recuerda:</p>
    <ul>
        <li>✅ 5€ por cada amigo que tramite su bono con Tu Trámite Fácil</li>
        <li>✅ Sin límite de usos</li>
        <li>✅ Transferencia directa</li>
    </ul>

    <p>Tu código sigue activo y tú sigues a tiempo de sacarle partido 😉</p>

    <p style="text-align: center; font-size: 18px;"><strong>https://app.tutramitefacil.es/{{ $user->ref_code ?? '[CÓDIGO_PERSONALIZADO]' }}</strong></p>

    {{-- Footer común --}}
    <p style="margin-top: 30px;"><strong>¿Tienes dudas o algún problema?</strong><br><br>
        Te ayudamos encantados:</p>

    @include('partials.email-contact-buttons')

    <p style="margin-top: 20px;">
        El equipo de <strong>Tu Trámite Fácil 💙</strong>
    </p>

@endsection
