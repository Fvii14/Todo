@extends('emails.EmailLayout')

@section('title', '🎉 ¡Has ganado 5€ por invitar a un amigo!')

@section('content')

    <!-- Previsualización oculta -->
    <span style="display: none; color: transparent; visibility: hidden; height: 0; opacity: 0;">
        ¡Un colega ha utilizado tu código personalizado!
    </span>

    <p>
        Ey{{ $referrerNombre ? ' ' . $referrerNombre : '' }}!<br>
    </p>



    <p>🥳 <strong>¡Tu código de referido ha sido utilizado con éxito!</strong></p>

    <p>
        👉 <strong>Has ganado 5€</strong> 🎉<br><br>
        En los próximos <strong>3-4 días hábiles</strong> recibirás una transferencia con tu recompensa.
    </p>

    {{-- <hr style="margin: 20px 0; border: none; border-top: 1px solid #ccc;"> --}}

    <p>Y recuerda:<strong> puedes seguir ganando más. </strong><br><br>
        Por cada persona que use tu código → <strong>otros 5€ para ti</strong>.
    </p>

    <p>Tu código sigue activo:<br><br>
        <strong>https://app.tutramitefacil.es/{{ $referrer->ref_code ?? '[CÓDIGO_PERSONALIZADO]' }}</strong>
    </p>

    <p>¡A por más!</p>

    {{-- Footer común --}}
    <p style="margin-top: 30px;"><strong>¿Tienes dudas o algún problema?</strong><br><br>
        Te ayudamos encantados:</p>

    @include('partials.email-contact-buttons')

    <p style="margin-top: 20px;">
        Sin complicaciones ni burocracia,<br><br>
        El equipo de <strong>Tu Trámite Fácil 💙</strong>
    </p>

@endsection
