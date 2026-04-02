@extends('emails.EmailLayout')

@section('title',
    match ($step) {
    1 => '¡Estás a un clic de conseguir 400€!',
    2 => '¿Qué harías con 400€ para gastar en cultura?',
    3 => '¡Hay 400€ que te pertenecen!',
    default => 'Tu Bono Cultural Joven te está esperando',
    })

@section('content')
    <span style="display: none; color: transparent; visibility: hidden; height: 0; opacity: 0;">
        {{ match ($step) {
            1 => 'Contrata tu Bono Cultural hoy mismo',
            2 => 'Con el Bono Cultural todo es posible',
            3 => 'Empieza tu Bono Cultural y no pierdas dinero',
            default => 'Bono Cultural Joven con Tu Trámite Fácil',
        } }}
    </span>

    <p>Hola {{ $nombrePila ?: '👋' }}</p>


    @if ($step == 1)
        <p>
            Has nacido en 2007, entonces te toca el <strong>Bono Cultural</strong>.<br><br>
            Pero… todavía no lo has pedido 😅
        </p>

        <p>
            En <strong>Tu Trámite Fácil</strong> te lo gestionamos sin líos y sin certificado digital.
        </p>

        <p>
            Encima ahora tenemos una promoción activa y te sale a un precio de risa:<br><br>
            💸 <strong>Solo 14,99 €</strong> con el código <strong>BONOTTF5</strong>
        </p>

        <p>
            Y eso no es todo: si lo tramitas con nosotros, te regalamos un código para que se lo pases a tus amigos y<strong> ganes
            5€</strong> por cada colega que pida su Bono Cultural con nosotros.
        </p>

        {{-- ✅ EMAIL 3 – Qué puedes hacer con los 400 € --}}
    @elseif ($step == 2)
        <p>Los <strong>400€</strong> del <strong>Bono Cultural</strong> están pensados para ti.</p>

        <p>¿Ideas para usarlos? Aquí van unas cuantas:</p>
        <ul>
            <p>-🎧 Spotify o Apple Music</p>
            <p>-📚 Libros de clase o de tus sagas favoritas</p>
            <p>-🎮 Videojuegos, Switch, Play o PC</p>
            <p>-🎬 Entradas de cine</p>
            <p>-🎟️ Festivales como Mad Cool, Arenal, Sonorama…</p>
            <p>-📱 Apps, teatro, exposiciones y más</p>
        </ul>

        <p>
            Todo eso puedes tenerlo con el <strong>Bono Cultural Joven</strong>.
        </p>

        <p>
            Con <strong>Tu Trámite Fácil</strong> lo gestionas en 2 minutos, sin certificado digital y <strong>solo por
                14,99€</strong> con el código <strong>BONOTTF5</strong>.
        </p>

        <p>
            Además, cuando termines el trámite, tendrás tu propio código de referido para invitar a amigos.<br><br>
            👉 Por cada colega que pida su bono con nosotros, te regalamos <strong>5€</strong>.
        </p>

        <p><em>Sin líos ni rollos, ¡empieza a ganar dinero hoy!</em></p>

        {{-- ✅ EMAIL 4 – Urgencia --}}
    @elseif ($step == 3)

        <p>
            Sabemos que te tocan los <strong>400€</strong>, pero… aún no has dado el paso.
        </p>

        <ul>
            <p>-🎯 Es una ayuda oficial, solo para jóvenes nacidos en 2007.</p>
            <p>-🕐 Solo puedes pedirlo una vez.</p>
            <p>-🚀 Nosotros lo hacemos por ti.</p>
        </ul>

        <p>
            Aprovecha la promo antes de que se te pase: pide tu bono sin papeleos y líos por <strong>14,99€</strong> con el
            código <strong>BONOTTF5</strong>.
        </p>

        <p>
            💸 Y cuando lo termines, te damos un código personal para que ganes <strong>5€</strong> por cada colega que lo
            tramite con nosotros.
        </p>

        <p><strong>👉 Pide tu bono aquí ya:</strong></p>
    @endif

    {{-- Botón de CTA común --}}
            <p style="text-align: center; margin: 30px 0;">
            <a href="https://app.tutramitefacil.es"
                style="background-color: #54debd; color: #3c3a60; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block;">
                ¡Quiero mi bono!
            </a>
        </p>

    <p>¡Hazlo ya! Que los 400€ no se ganan todos los días…</p>

    <p>
        El equipo de <strong>Tu Trámite Fácil 💙</strong>
    </p>
@endsection
