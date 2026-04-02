@extends('emails.EmailLayout')

@section('title',
    match ($step) {
        1 => '💸 ¿Quieres ganar 5€ con tu código?',
        2 => '📢 ¡Tu código sigue activo!',
        3 => '🎯 Aún estás a tiempo de ganar con tu código',
        4 => '⏳ Última oportunidad para ganar 5€ por referido',
        default => 'Gana 5€ por invitar a tus amigos',
    }
)

@section('content')

    {{-- Previsualización oculta --}}
    <span style="display: none; color: transparent; visibility: hidden; height: 0; opacity: 0;">
        {{ match ($step) {
            1 => 'Tu código personalizado puede darte dinero',
            2 => 'Comparte tu código y gana dinero fácil',
            3 => 'Aún no han usado tu código. ¡Muévelo!',
            4 => 'Última oportunidad para ganar 5€ por cada amigo',
            default => 'Tu código puede darte 5€ por cada amigo',
        } }}
    </span>

    <p>Hola {{ $nombre ?? '👋' }},</p>

    @if ($step === 1)
        <p>Ya tienes tu <strong>Bono Cultural</strong> en marcha, pero...</p>
        <p><strong>¿Vas a quedarte ahí?</strong> Porque ahora puedes sacarte unos euros extra 🤑</p>
        <p><strong>👉 5€ por cada amigo</strong> que use tu código para tramitar su bono.</p>

        <p>Este es tu código:</p>
        <p style="text-align: center; font-size: 18px;"><strong>https://app.tutramitefacil.es/{{ $codigo }}</strong></p><br>

        <p>Compártelo, postéalo, mándaselo a tu grupo del insti… lo que quieras.</p>
        <p>👉 Si lo usan, tú ganas. Fácil y sin hacer nada más 😉</p>

    @elseif ($step === 2)
        <p><strong>Laura</strong> empezó como tú: tramitó su bono, recibió su código…</p>
        <p>Y en 3 días ya había ganado <strong>20€</strong> compartiéndolo con colegas.</p>
        <p>¿Tú cuántos tienes en tu grupo de WhatsApp?</p>

        <p>Tu código:</p>
        <p style="text-align: center; font-size: 18px;"><strong>https://app.tutramitefacil.es/{{ $codigo }}</strong></p><br>
        <p>Por cada uno que lo use → <strong>5€ para ti</strong></p>
        <p>💥 Trámite fácil + dinerito extra = <strong>win total</strong></p>

    @elseif ($step === 3)
        <p>Tienes en tu mano un código que te da <strong>dinero 💸</strong>,</p>
        <p>pero aún nadie lo ha usado…</p>

        <p>Cada amigo que lo use → <strong>5€ para ti</strong></p>
        <p>Solo tienen que poner este código:</p>

        <p style="text-align: center; font-size: 18px;"><strong>https://app.tutramitefacil.es/{{ $codigo }}</strong></p>

        <p>¡Haz que lo usen! Este mes estamos haciendo las transferencias <strong>más rápido</strong> 😉</p>

    @elseif ($step === 4)
        <p>¿Sabes qué es lo mejor de tener tu bono cultural ya tramitado?</p>
        <p><strong>👉 ¡Que ahora puedes ganar dinero solo compartiendo tu código!</strong></p>

        <p>Este es el tuyo:</p>
        <p style="text-align: center; font-size: 18px;"><strong>https://app.tutramitefacil.es/{{ $codigo }}</strong></p>

        <p>¿5€ por cada amigo que lo use?<br><br>
        ¿Sin hacer nada más?<br><br>
        ¿Transferencia directa?</p>

        <p><strong>No lo dejes pasar.</strong> Haz que rule y gana 💸</p>
    @endif

    <p style="margin-top: 30px;"><strong>¿Tienes dudas o algún problema?</strong><br><br>
        Te ayudamos encantados:</p>

    @include('partials.email-contact-buttons')

    <p style="margin-top: 20px;">
        El equipo de <strong>Tu Trámite Fácil 💙</strong>
    </p>

@endsection
