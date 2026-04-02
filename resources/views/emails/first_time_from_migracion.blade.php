
@extends('emails.EmailLayout')

@section('content')
    <h2>Hola, {{ $user->name }}</h2>

    <p>Queremos darte una gran noticia: <strong>hemos renovado nuestra plataforma</strong> para que tu experiencia con Tu Trámite Fácil sea mucho más sencilla, rápida y segura.</p>

    <p>Como parte de esta mejora, <strong>hemos restablecido las contraseñas de todos los usuarios</strong>. Para que puedas entrar sin complicaciones, hemos asignado como contraseña temporal <strong>tu DNI o NIE.</strong></p>

    <p><strong>🔐 ¿Qué tienes que hacer? 🔐</strong></p>

    <p>
        1. Entra en la nueva plataforma con tu <strong>email y tu DNI o NIE</strong> como contraseña.
    </p>

    <p>
        2. <strong>Cambia tu contraseña cuanto antes</strong> por una nueva y segura (¡es muy importante para proteger tu cuenta!).
    </p>

    <p>
        3. Ya estás listo/a para usar la nueva plataforma.
    </p>

    <div style="text-align: center; margin: 24px 0;">
        <a href="https://app.tutramitefacil.es"
           style="display: inline-block; padding: 12px 28px; background-color: #54debd; color: #003366; border: 2px solid #003366; border-radius: 30px; text-decoration: none; font-weight: bold; font-size: 16px;">
            Accede a tu perfil ahora
        </a>
    </div>

    <p>
        Te agradecemos mucho por tu paciencia mientras hacemos estos ajustes. Sabemos que cambiar puede generar dudas, pero te aseguramos que <strong>todo está bajo control</strong> y que estamos trabajando para ofrecerte una experiencia aún más fluida y rápida en el futuro. 💙
    </p>

    <p>
        Si tienes alguna pregunta o te surge cualquier duda, <strong>no dudes en contactarnos</strong>. ¡Estamos aquí para ayudarte!
    </p>

    <p>
        Gracias por elegirnos para tramitar tus ayudas. Estamos emocionados de seguir acompañándote en cada paso de este proceso.
    </p>

    <p>
        Un abrazo,
    </p>

    <p>
        El equipo de Tu Trámite Fácil.
    </p>
@endsection