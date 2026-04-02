@extends('emails.EmailLayout')

@section('content')
    <div class="email-header">
        <h2><strong>Hola</strong>, {{ $user->name }}</h2>
    </div>

    <p>
        Gracias por registrarte en <strong>Tu Trámite Fácil</strong>.
    </p>

    <p>
        Para completar tu registro, por favor verifica tu dirección de correo electrónico haciendo clic en el siguiente botón:
    </p>

    <p style="text-align: center;">
        <a href="{{ $verificationUrl }}" 
            class="email-button"
            style="background-color:#54debd;color:white;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;font-weight:bold;">
            Verificar mi correo electrónico
        </a>
    </p>

    <p>
        Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:
    </p>

    <p style="word-break: break-all; color: #54debd;">
        {{ $verificationUrl }}
    </p>

    <p>
        <strong>Nota:</strong> Este enlace expirará en 24 horas por seguridad.
    </p>

    <p>
        Si no creaste una cuenta en Tu Trámite Fácil, puedes ignorar este correo de forma segura.
    </p>

    <p>
        👉 ¿Dudas? Responde a este email y nuestro equipo de asesores te ayudará.
    </p>

    <p>
        O si lo prefieres, puedes contactar con nosotros:
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

