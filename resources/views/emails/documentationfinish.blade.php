@extends('emails.EmailLayout')

@section('content')
    <h2>Hola, {{ $user->name }}</h2>

    <h3>
        ¡Gracias por completar la documentación!
    </h3>

    <p>
        La tramitación de tu ayuda <strong>{{ $ayuda->nombre_ayuda }}</strong> ya está en marcha.
    </p>

    <p>
        Puedes comprobar en qué estado se encuentra en tu área personal.
    </p>

    <p>
        Un saludo.
    </p>
@endsection