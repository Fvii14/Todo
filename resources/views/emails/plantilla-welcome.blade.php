@extends('emails.EmailLayout')

@section('content')
    <div class="email-header">
        <h2><strong>Hola</strong>, {{ $name }}</h2>
    </div>

    @if ($step === 1)
        <p>
            ¡Enhorabuena por crear tu perfil en <strong>Tu Trámite Fácil</strong>!
        </p>

        <p>
            Ahora, el siguiente paso es <strong>conectarte con Cl@ve</strong>, el sistema oficial de
            verificación de identidad de la administración pública:
        </p>

        <p>
            🚀 <strong>Analizamos tu situación en segundos</strong> — sin búsquedas, sin papeles y sin
            complicaciones:
        </p>

        <p>
            🔒 <strong>Tecnología segura,</strong> empleada por administraciones y empresas líderes:
        </p>

        <ul>
            <li>
                <strong>Informe de vida laboral:</strong> Confirmamos tu historial de cotización para
                validar requisitos.
            </li>
            <li>
                <strong>Declaración de la renta:</strong> Cruzamos tus ingresos con los umbrales de
                ayudas.
            </li>
        </ul>

        <p>
            Si prefieres no usar Cl@ve, puedes completar un <strong>formulario manual</strong> (menos
            ágil y más laborioso) al final de esta pantalla
        </p>

        <p>
            <strong>¿No sabes cómo hacer la conexión o cómo conseguir tu Cl@ve PIN?</strong>
        </p>

        {{-- <p>
            Te lo enseñamos en este vídeo:
            ToDo: Añadir vídeo cuando esté
        </p> --}}

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
    @elseif ($step === 2)
        <p>
            Ayer diste el primer paso para descubrir <strong>todas las ayudas que te
                pertenecen</strong>, pero aún no te has conectado con
            Cl@ve.
        </p>

        <p>
            🔹 <strong>Sin búsquedas, sin papeles, en segundos</strong> sabes a qué ayudas puedes
            optar.<br>
            🔹 Tecnología de verificación <strong>segura y usada por administraciones y empresas
                líderes.</strong>
        </p>

        <p>
            Si conectas ahora:
        </p>

        <ol>
            <li>Accedemos a tu vida laboral y confirmamos tus cotizaciones.</li>
            <li>Cruzamos tus ingresos con los umbrales para cada ayuda.</li>
            <li>Te decimos al instante cuáles son las que puedes solicitar.</li>
        </ol>

        <p>
            ❗️ <strong>Actúa ya:</strong> el plazo para muchas convocatorias está abierto y cada día
            cuenta.
        </p>

        <p>
            Si no te sientes cómodo con Cl@ve, siempre puedes completar el <strong>formulario
                manual</strong>.
        </p>

        <p>
            <strong>¿No sabes cómo hacer la conexión o cómo conseguir tu Cl@ve PIN?</strong>
        </p>

        {{-- <p>
            Te lo enseñamos en este vídeo. 📹
            ToDo: Añadir vídeo cuando esté
        </p> --}}

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
    @elseif ($step === 3)
        <p>
            Entendemos que puede generar dudas conectar con Cl@ve, pero es la vía <strong>más rápida y
                segura</strong> para conocer <strong>en segundos</strong> todas las ayudas públicas a
            las que tienes derecho.
        </p>

        <p>
            🔒 <strong>Cl@ve es oficial</strong> y la usan administraciones y empresas líderes para
            verificar identidad de forma fiable.
        </p>

        <p>
            ⏱️ <strong>Menos de 5 minutos:</strong> accede a tu información real sin papeles ni
            desplazamientos.
        </p>

        <p>
            💡 <strong>Resultado instantáneo:</strong> te mostramos tu listado personalizado de ayudas,
            sin que tengas que buscar nada.
        </p>

        <p>
            Si aún no te has animado, piensa en esto: cada día que pasa puede marcar la diferencia en el
            plazo de solicitud de muchas convocatorias.
        </p>

        <p>
            <strong>¿Tienes dudas sobre cómo funciona?</strong>
        </p>

        <ul>
            <li>
                Conecta con Cl@ve PIN.
            </li>
            <li>
                Sigue las instrucciones paso a paso en pantalla.
            </li>
            <li>
                Si algo no encaja, nuestro equipo te ayuda al instante por Whatsapp o llamada.
            </li>
        </ul>

        <p>
            Y recuerda, si no quieres usar Cl@ve, siempre puedes optar por el <strong>formulario
                manual</strong>.
        </p>

        <p>
            <strong>¿No sabes cómo hacer la conexión o cómo conseguir tu Cl@ve PIN?</strong>
        </p>

        {{-- <p>
            Te lo enseñamos en este video. 📹
            ToDo: Añadir vídeo cuando esté
        </p> --}}

        <p>
            👉¿Dudas? Responde a este email y nuestro equipo de asesores te ayudará.
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
    @elseif ($step === 4)
        <p>
            Ha pasado una semana desde que creaste tu perfil y <strong>aún no has descubierto las ayudas
                que te pertenecen</strong>.
        </p>

        <p>
            ❓ Imagina renunciar a un dinero aprobado para ti por no completar un paso tan rápido…
        </p>

        <p>
            🔹 En un <strong>clic</strong> con Cl@ve: sabes al instante qué ayudas puedes solicitar.<br>
            🔹 Sin riesgos: tecnología oficial y verificada.<br>
            🔹 Sin papeles: olvídate de desplazamientos y trámites.
        </p>

        <p>
            Si no conectas con Cl@ve, puedes hacerlo con nuestro <strong>formulario manual</strong>.
        </p>

        <p>
            ⏳ <strong>Este es tu último recordatorio:</strong> aprovecha la forma <strong>más ágil y
                segura</strong> para descubrir y tramitar las ayudas que te corresponden lo que te
            corresponde.
        </p>

        <p>
            <strong>¿No sabes cómo hacer la conexión o cómo conseguir tu Cl@ve PIN?</strong>
        </p>

        {{-- <p>
            Te lo enseñamos en este video. 📹
            ToDo: Añadir vídeo cuando esté
        </p> --}}

        <p>
            👉¿Dudas? Responde a este email y nuestro equipo de asesores te ayudará.
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
    @else
        <p>Gracias por tu interés en Tu Trámite Fácil.</p>
        <p>Contactaremos contigo pronto</p>
    @endif
@endsection
