@extends('emails.EmailLayout')

@section('content')
    <h2>Hola {{ $name }},</h2>

    <p>Queremos informarte que el estado de tu solicitud para la ayuda <strong>{{ $nombre_ayuda }}</strong> ha sido
        actualizado.</p>

    <p>Estado actual: <strong>{{ ucfirst($nuevo_estado) }}</strong></p>

    <p>Si necesitas más información, puedes acceder a tu perfil para revisar los detalles de tu tramitación.</p>

    <a href="https://tutramitefacil.es/historial-ayudas" class="email-button">
        Ver mi solicitud
    </a>

    <p>Gracias por confiar en Tu Trámite Fácil.</p>
@endsection
