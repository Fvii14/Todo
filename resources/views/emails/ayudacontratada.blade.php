@extends('emails.EmailLayout')

@section('content')
    @if ($step === 1)
        <h2>Hola {{ $name }},</h2>
        
        <p>
            ¡Genial! Ahora que eres beneficiario, el siguiente paso es subir la documentación que necesitamos para tramitar tu <strong>{{ $ayuda->nombre_ayuda }}</strong>. 🙌
        </p>

        <p>
            Si la subes en menos de 7 días, te regalamos 100€ que te descontamos del porcentaje que te concedan de la ayuda.
        </p>

        <p>
            👉 ¿Cómo subir la documentación?
        </p>

        <ol>
            <li>Accede a tu área de cliente.</li>
            <li>En la sección Mis ayudas, encontrarás la lista de archivos que debes enviar.</li>
            <li>Sube cada documento directamente desde tu móvil o tu ordenador.</li>
        </ol>

        <p>
            📁 Recuerda: cuanta más completa esté tu documentación, más rápido podremos presentar tu solicitud y asegurarte el ingreso de <strong>{{ $ayuda->cuantia_usuario }}€</strong> en tu cuenta.
        </p>

        <!-- Botón principal -->
        <p style="text-align: center;">
            <a href="{{ route('user.AyudasSolicitadas') }}" 
               style="background-color:#28a745;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;">
                ▶️ Subir mis documentos ahora
            </a>
        </p>

        <p>
            Si tienes cualquier problema técnico o necesitas asistencia, estamos aquí para ayudarte:
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
            Sin complicaciones ni burocracia<br>
            El equipo de Tu Trámite Fácil
        </p>
    @elseif ($step === 2)
        <h2>Hola {{ $name }},</h2>

        <p>
            Sabemos lo que pasa por tu cabeza:
        </p>

        <p>
            1️⃣ <strong>Precio:</strong> "¿La tarifa de TTF no será excesiva?"
        </p>

        <p>
            Con TTF, solo pagas si recibes tu ayuda. Nuestro modelo "éxito compartido" te evita facturas adelantadas y reduce cualquier riesgo.
        </p>

        <p>
            2️⃣ <strong>Desconfianza:</strong> "¿Y si la gestión no va bien?"
        </p>

        <p>
            Más de 1.200 usuarios han confiado en nosotros en el último año. Nuestro proceso es supervisado por abogados, y cada paso está validado legalmente.
        </p>

        <p>
            3️⃣ <strong>Hacerlo por mi cuenta:</strong> "Podría intentarlo solo y ahorrar ese porcentaje…"
        </p>

        <p>
            Imagina dedicar horas a llamar a oficinas, recopilar documentos y reconfirmar citas. Con TTF te quitas un marrón burocrático y recuperas ese tiempo para lo que realmente importa.
        </p>

        <p>
            📑 Piensa en Marta, que quiso hacerlo sola y tardó 3 semanas en recopilar papeles, solo para descubrir que faltaba un sello. Con TTF, en menos de 5 días y sin errores, tendrás todo listo.
        </p>

        <p>
            ¿List@ para liberarte de la burocracia?
        </p>

        <p>
            👉 Sube los documentos que faltan en tu área personal y nos pondremos manos a la obra.
        </p>

        <p>
            ⚠️ ¡Date prisa! El plazo para completar el trámite es limitado y cada día cuenta
        </p>

        <p>
            Recuerda que si los subes en menos de 7 días, te regalamos 100€ que te descontamos del porcentaje que te concedan de la ayuda.
        </p>

        <p>
            Si surge cualquier inconveniente, contáctanos de inmediato y lo solucionamos juntos
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
            Sin complicaciones ni burocracia<br>
            El equipo de Tu Trámite Fácil
        </p>
    @elseif ($step === 3)
        <h2>Hola {{ $name }},</h2>

        <p>
            No hay mejor manera de entender cómo trabajamos que escuchar a quienes ya han pasado por esto:
        </p>

        <p>
            <strong>Ricardo F.</strong><br>
            ⭐⭐⭐⭐⭐<br>
            "Servicio excelente y recomendable. Me acompañaron Mario y Pablo con rapidez y profesionalismo absoluto. ¡Gracias por hacerlo todo tan sencillo!"
        </p>

        <p>
            <strong>Vincent Costa</strong><br>
            ⭐⭐⭐⭐⭐<br>
            "Necesitaba ayuda con la solicitud del alquiler y no sabía ni por dónde empezar. Con ellos todo fue online y súper claro. Me explicaron bien cada paso y la documentación que necesitaban, no tuve que preocuparme de nada. Muy recomendable si quieres evitarte líos."
        </p>

        <p>
            <strong>Antonio</strong><br>
            ⭐⭐⭐⭐⭐<br>
            "Tuve una experiencia excelente con el servicio de trámites de ayudas. Todo el proceso fue rápido, sencillo y muy bien gestionado. La atención fue excelente, resolvieron todas mis dudas y me guiaron en cada paso. Sin duda, recomiendo este servicio a quien necesite realizar gestiones de ayudas de manera eficiente y sin complicaciones."
        </p>

        <p>
            Cada una de estas experiencias muestra cómo, con TTF, tú también puedes olvidarte del papeleo y centrarte en lo que de verdad importa.
        </p>

        <p>
            ¿Quieres ver cómo podemos ayudarte a ti también?
        </p>

        <p>
            🗂️ <strong>Sube la documentación</strong><br>
            En el siguiente paso te indicaremos exactamente qué documentos necesitamos (DNI, justificantes de ingresos, etc.).<br>
            Sube todo directamente desde tu área personal; en segundos y sin complicaciones.
        </p>

        <p>
            Si surge cualquier inconveniente, contáctanos de inmediato y lo solucionamos juntos.
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
            ⏳ No dejes pasar más días: tu ayuda está esperándote y los plazos corren.
        </p>

        <p>
            Sin complicaciones ni burocracia<br>
            El equipo de Tu Trámite Fácil
        </p>
    @elseif ($step === 4)
        <h2>Hola {{ $name }},</h2>

        <p>
            Hace poco, un cliente nos dijo: "Invertí más de 10 horas en trámites y todavía no tengo noticias de la resolución".
        </p>

        <p>
            Imagínate esto:
        </p>

        <ul>
            <li>5 llamadas telefónicas a diferentes oficinas.</li>
            <li>12 documentos que imprimir, sellar y escanear.</li>
            <li>Consultas interminables para verificar requisitos cambiantes.</li>
        </ul>

        <p>
            ✅ <strong>Con TTF:</strong>
        </p>

        <ul>
            <li>Solicitamos oficialmente tu ayuda bajo supervisión legal.</li>
            <li>Te informamos de cada avance vía email o llamada.</li>
            <li>Recibes tu ingreso en tu cuenta sin mover un papel.</li>
        </ul>

        <p>
            👉 ¿El resultado? Ahorras al menos 8 horas y evitas el estrés de la gestión.
        </p>

        <p>
            ⏰ No dejes pasar ni un minuto más: sube la documentación en tu área personal para que podamos comenzar con la tramitación de tu <strong>{{ $ayuda->nombre_ayuda }}</strong>.
        </p>

        <p>
            Si tu tiempo es valioso, deja que trabajemos por ti.
        </p>

        <p>
            Para resolver cualquier duda, nuestro equipo está para asesorarte:
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
            Sin complicaciones ni burocracia<br>
            El equipo de Tu Trámite Fácil
        </p>
    @elseif ($step === 5)
        <h2>Hola {{ $name }},</h2>

        <p>
            ⚠️ Este es un recordatorio importante: el plazo para solicitar tu ayuda de <strong>{{ $ayuda->cuantia_usuario }}€</strong> es limitado.
        </p>

        <p>
            Dejar pasar este trámite significa renunciar a un dinero que ya está aprobado.
        </p>

        <p>
            ✅ ¿Qué ganas con TTF?
        </p>

        <ul>
            <li>Pago con éxito: Solo pagas si cobras.</li>
            <li>Supervisado por abogados: Garantía legal en cada paso.</li>
            <li>Tramitación automática: Menos de 5 minutos de tu tiempo.</li>
            <li>Ahorra tiempo: Evita desplazamientos y llamadas.</li>
        </ul>

        <p>
            🔥 <strong>Proceso record:</strong>
        </p>

        <ol>
            <li>Análisis y cruce de datos.</li>
            <li>Solicitud oficial.</li>
            <li>Seguimiento personalizado.</li>
            <li>Transferencia directa a tu cuenta.</li>
        </ol>

        <p>
            Recuerda: cada día que pasa, aumenta el riesgo de quedarte sin esta ayuda.
        </p>

        <p>
            ¿Te ayudamos en estos últimos pasos? Sube la documentación que falta a tu área personal para que podamos comenzar con la tramitación de tu <strong>{{ $ayuda->nombre_ayuda }}</strong>.
        </p>

        <p>
            Nuestro equipo está para asesorarte:
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
            Sin complicaciones ni burocracia<br>
            El equipo de Tu Trámite Fácil
        </p>
    @endif
@endsection