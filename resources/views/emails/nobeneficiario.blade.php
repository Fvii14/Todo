@extends('emails.EmailLayout')

@section('content')
    <h2>Hola, {{ $name }}</h2>

    <h3>
        ¡Lo sentimos!
    </h3>

    <p>
        Según las respuestas que nos has dado, no te merece la ayuda <strong>{{ $ayuda->nombre_ayuda }}</strong>. Aún así, comprobaremos manualmente tu solicitud por si podemos hacer algo.
    </p>

    <p>
        Gracias! Permanecemos en contacto contigo.
    </p>
@endsection