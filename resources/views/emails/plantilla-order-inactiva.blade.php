@extends('emails.EmailLayout')

@section('content')
    <h2>¡Gracias por confiar en nosotros, {{ $name }}!</h2>

    <p>Hemos recibido tu solicitud para que tramitemos por ti <strong>"{{ $nombre_ayuda }}"</strong>. Actualmente, el plazo para solicitar esta ayuda en el presente año ya ha finalizado.</p>

    <p>Pero no te preocupes: <strong>tu solicitud ha quedado registrada</strong> y te daremos prioridad para tramitarla automáticamente en cuanto se abra el nuevo plazo el próximo año.</p>

    <p>Este servicio tiene un coste total de <strong>{{ number_format($total, 2) }}€</strong>, que se mantendrá sin cambios cuando se procese tu ayuda.</p>

    <p>Nuestro equipo te mantendrá informado/a y te notificará en cuanto se active la ayuda nuevamente.</p>

@endsection
