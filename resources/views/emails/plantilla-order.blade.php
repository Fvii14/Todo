@extends('emails.EmailLayout')

@section('content')
    <h2>¡Gracias por confiar en nosotros, {{ $name }}!</h2>

<p>Hemos recibido tu solicitud para que tramitemos por ti "{{ $nombre_ayuda }}" con éxito, nuestro equipo se pondrá pronto en contacto contigo.</p>

<p>Total del servicio: <strong>{{ number_format($total, 2) }}€</strong></p>

<p>Esto es solo una confirmación de que hemos recibido correctamente tu solicitud de tramitación.</p>

@endsection
