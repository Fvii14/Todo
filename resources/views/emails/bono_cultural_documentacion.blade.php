@extends('emails.EmailLayout')

@section('title',
    match ($step) {
    1 => '📄 No te olvides de subir tus documentos',
    2 => '🚨 Último paso para recibir tu ayuda',
    3 => '📢 ¡Estás dentro! Ahora toca mover ficha',
    default => '⚠️ Documentación pendiente para tu ayuda',
    })

@section('content')

    <!-- Previsualización oculta -->
    <span style="display: none; color: transparent; visibility: hidden; height: 0; opacity: 0;">
        {{ match ($step) {
            1 => 'Y recuerda que tu código ya está activo 💥',
            2 => 'Sube tus documentos y activa tu recompensa 💸',
            3 => 'Documentos + gana pasta con tu código 😎',
            default => 'Te regalamos dinero por cada amigo que traes 💶',
        } }}
    </span>

   <p>Hola {{ $nombrePila ?: '👋' }}</p>

    
    @if ($step == 1)
        <p>Gracias por tramitar tu <strong>Bono Cultural Joven</strong> con nosotros 🙌</p>
        <p>Ya estás a un paso de disfrutar tus <strong>400€</strong> para cultura.</p>
        <p>Ahora, ¡tienes la oportunidad de ganar más dinero 💸!</p>
        <p><strong>👉 ¿Cómo subir la documentación?</strong><br><br>
            Accede a tu área de cliente.<br><br>
            En la sección <strong>“Ayudas solicitadas”</strong>, encontrarás la lista de archivos que debes enviar.<br><br>
            Sube cada documento directamente desde tu móvil o tu ordenador.</p>

        <p>Cuanto más completa esté tu documentación, más rápido podremos presentar tu solicitud y conseguir tus 400 € para
            cultura.</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="https://app.tutramitefacil.es/ayudas-solicitadas"
                style="background-color: #3c3b60; color: white; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block;">
                Subir mis documentos ahora
            </a>
        </p>

        <p><strong>No lo olvides:</strong> puedes ganar dinero.<br><br>
            🤑 <strong>5€ por cada amigo</strong> que use tu código para tramitar su bono.</p>

        <p>Tu código: <strong>https://app.tutramitefacil.es/{{ $user->ref_code ?? '[CÓDIGO_PERSONALIZADO]' }}</strong></p>

        <p>Compártelo, postéalo, mándaselo a tu grupo del insti… ¡lo que quieras!</p>

        {{-- EMAIL 6.2 – Recordatorio suave --}}
    @elseif ($step == 1)
        <p>¡Ya casi está! Solo nos falta la documentación para poder tramitar tu ayuda.</p>

        <p>Es muy fácil y solo te llevará unos minutos:</p>
        <ul>
            <li>✅ Entra en tu área de cliente</li>
            <li>✅ Accede a "Ayudas solicitadas”"</li>
            <li>✅ Sube tus documentos desde el móvil o el ordenador</li>
        </ul>

        <p style="text-align: center; margin: 30px 0;">
            <a href="https://app.tutramitefacil.es"
                style="background-color: #3c3b60; color: white; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block;">
                Subir mis documentos ahora
            </a>
        </p>

        <p>🎁 ¡Tu código de referido ya está activo!<br><br>
            Tu código: <strong>https://app.tutramitefacil.es/{{ $user->ref_code ?? '[CÓDIGO_PERSONALIZADO]' }}</strong></p>

        <p>Hazlo viral: grupo del insti, stories, WhatsApp… ¡lo que quieras!</p>


    @elseif ($step == 2)
        <p>Este es el último paso para completar tu solicitud del <strong>Bono Cultural Joven</strong>.</p>

        <p>Solo tienes que subir los documentos desde tu área de cliente:</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="https://app.tutramitefacil.es/ayudas-solicitadas"
                style="background-color: #3c3b60; color: white; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block;">
                Subir mis documentos ahora
            </a>
        </p>

        <p>Cuanto antes los tengamos, antes podremos tramitar tu bono de 400€ para cultura.</p>

        <p>🤑 Y ya tienes activo tu código de recomendación:<br><br>
            <strong>https://app.tutramitefacil.es/{{ $user->ref_code ?? '[CÓDIGO_PERSONALIZADO]' }}</strong>
        </p>

        <p>Por cada amigo que lo use,<strong> te ingresamos 5€</strong>. Compártelo y gana dinero mientras esperas tu ayuda 🎉</p>
        <p>Sin complicaciones ni burocracia,<br></p>
        {{-- EMAIL 6.4 – Estilo joven y directo --}}
    @elseif ($step == 3)
        <p>¡Estás dentro! Ya puedes pedir tu <strong>Bono Cultural Joven</strong>…</p>

        <p>Pero antes necesitamos tus documentos:</p>
        <ul>
            <p>1. <strong>Entra</strong> en tu área de cliente</p>
            <p>2. <strong>Sube</strong> todo lo que te pedimos</p>
            <p>3. Cuanto antes lo hagas, antes pillas tus <strong>400€</strong></p>
        </ul>


        <p style="text-align: center; margin: 30px 0;">
            <a href="https://app.tutramitefacil.es/ayudas-solicitadas"
                style="background-color: #3c3b60; color: white; padding: 14px 24px; border-radius: 8px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block;">
                Subir mis documentos ya
            </a>
        </p>

        <p>💸 Tu código está activado:<br><br>
            <strong>https://app.tutramitefacil.es/{{ $user->ref_code ?? '[CÓDIGO_PERSONALIZADO]' }}</strong>
        </p>

        <p>Por cada colega que se apunte contigo → <strong>5€ para ti.</strong><br>
            Hazlo correr. Stories, grupos de WhatsApp, lo que sea.</p>

        
    @endif

    {{-- Footer común --}}
    <p style="margin-top: 30px;"><strong>¿Tienes dudas o algún problema?</strong><br><br>
        Te ayudamos encantados:</p>

    @include('partials.email-contact-buttons')
        
    <p style="margin-top: 20px;">
        El equipo de <strong>Tu Trámite Fácil 💙</strong>
    </p>

@endsection
