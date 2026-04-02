@extends('emails.EmailLayout')

@section('content')
    <h2>Hola {{ $name }},</h2>

    <p>
        ¡Enhorabuena! 🎉
    </p>

    <p>
        Eres <strong>beneficiario</strong> de <strong>{{ $ayuda->nombre_ayuda }}</strong>, por un importe de
        <strong>{{ $cuantia_total }}€</strong>.
    </p>

    <p>
        👉 No dejes pasar esta oportunidad: estás a punto de recibir un impulso económico que te corresponde.
    </p>

    <p>
        <strong>¿Qué debes hacer ahora?</strong>
    </p>

    <p>
        En tu <strong>área personal </strong> encontrarás dos pasos muy sencillos:
    </p>

    <p>
        <strong>📁 Subir la documentación</strong>
    </p>

    <ul>
        <li>
            En el siguiente paso te indicaremos exactamente qué documentos necesitamos (DNI, justificantes de ingresos,
            etc.)
        </li>
        <li>
            <strong>Sube todo directamente desde tu área personal;</strong> en segundos y sin complicaciones
        </li>
    </ul>

    <p style="text-align: center; margin: 30px 0;">
        <a href="https://app.tutramitefacil.es/ayudas-solicitadas"
            style="background-color: #3c3b60; color: white; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block;">
            Subir mis documentos ya
        </a>
    </p>

    <p>
        <strong>⚠️ Recuerda: </strong> cuanto antes completes estos pasos, antes podemos tramitar tu ayuda y antes tendrás
        tu dinero.
    </p>

    <p>
        ¡Completa ya tu solicitud para evitar retrasos!
    </p>

    <p>
        Si surge cualquier inconveniente, contáctanos de inmediato y lo solucionamos juntos.
    </p>

    <p>
        Si prefieres que te guiemos paso a paso, puedes contactar con nosotros:
    </p>

    <p style="text-align: center;">
        <a href="tel:+34603236800"
            style="background-color:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin:5px;display:inline-block;">
            📞 Llámanos
        </a><br>

        <a href="https://wa.me/34603236800"
            style="background-color:#25d366;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin:5px;display:inline-block;">
            💬 Hablar por WhatsApp
        </a>
    </p>

    <p>
        Sin complicaciones ni burocracia
    </p>

    <p>
        El equipo de Tu Trámite Fácil
    </p>
@endsection
