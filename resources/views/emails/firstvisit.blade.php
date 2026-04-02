@extends('emails.EmailLayout')

@section('content')
    <div class="email-header">
        <h2><strong>Hola</strong> {{ $name }},</h2>
    </div>

    @if ($step === 1)
        <p>
            Estas son <strong>las ayudas de las que eres posible beneficiario</strong>.
        </p>

        <ul>
            @foreach ($ayudasFiltradas ?? [] as $ayuda)
                <li>
                    {{ $ayuda->nombre_ayuda }}
                </li>
            @endforeach
        </ul>

        <p>
            🔍 ¿Y ahora qué?
        </p>

        <p>
            Para cada ayuda, debes rellenar <strong>el formulario específico con el que te confirmaremos
                si eres beneficiario o no</strong>.
        </p>

        <p>
            Solo tardarás unos minutos en cada uno y con ello:
        </p>

        <ul>
            <li>📑 Garantizamos que tu solicitud sea válida.</li>
            <li>⚙️ Nosotros revisamos y presentamos TODO sin errores.</li>
            <li>💸 Te aseguras de cobrar tu ayuda en el plazo establecido.</li>
        </ul>

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
            Hace un día que te enviamos el listado de <strong>todas las ayudas</strong> que te
            corresponden, pero todavía no has completado los formularios para cada una.
        </p>

        <p>
            🔹 <strong>Recuerda:</strong> cada ayuda se solicita con un formulario específico.<br>
            🔹 <strong>Completarlo solo te llevará unos minutos</strong> y garantiza que empecemos a
            tramitar tu ayuda cuanto antes.
        </p>

        <p>
            ¿Por qué no dejarlo para mañana?
        </p>

        <ul>
            <li>Los plazos pueden cerrarse antes de lo que imaginas. 🗓️</li>
            <li>Cuanto antes rellenes, antes recibes tu dinero. 💶</li>
            <li>Con TTF evitas errores y retrasos: nosotros revisamos todo. ✅</li>
        </ul>

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
    @elseif ($step === 3)
        <p>
            Ya llevas dos días sin completar los formularios necesarios para solicitar tus ayudas.
        </p>

        <p>
            ¡No dejes que se te escape esta oportunidad!
        </p>

        <p>
            <strong>¿Qué ganas al rellenarlos hoy?</strong>
        </p>

        <ul>
            <li>📝 <strong>Formularios adaptados:</strong> cómodos, directos y sin datos irrelevantes.
            </li>
            <li>📆 <strong>Plazos seguros:</strong> aseguramos tu solicitud antes de que cierre la
                convocatoria.</li>
            <li>💸 <strong>Dinero en tu cuenta:</strong> nosotros nos ocupamos del resto.</li>
        </ul>

        <p>
            Con Tu Trámite Fácil:
        </p>

        <ul>
            <li>Te avisamos de cualquier error al momento.</li>
            <li>Supervisión legal en cada paso.</li>
            <li>Pagas solo si cobras.</li>
        </ul>

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
    @elseif ($step === 4)
        <p>
            👉 Este es el <strong>último recordatorio</strong>.
        </p>

        <p>
            Si no completas cada formulario en las próximas horas, podrías perder el derecho a estas
            ayudas confirmadas.
        </p>

        <p>
            ⚠️ <strong>Plazo límite muy próximo:</strong> muchos procesos cierran sin aviso previo.<br>
            ⏰ <strong>Actúa ya</strong> para asegurar tu solicitud.
        </p>

        <p>
            Cómo proceder:
        </p>

        <ol>
            <li>Inicia sesión en tu área personal.</li>
            <li>Rellena los formularios de las ayudas de las que eres potencial beneficiario.</li>
            <li>Nosotros nos encargamos de validar y presentar tu solicitud.</li>
        </ol>

        <p>
            No dejes pasar lo que ya es tuyo.
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
    @endif
@endsection
