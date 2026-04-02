@php
    $fase = $ayudaSolicitada->fase ?? 'resolucion';
    $esConcedida = $fase === 'resolucion';
    $esRechazada = $fase === 'rechazada';

    // Obtener el documento de resolución si existe
    $documentoResolucion = $ayudaSolicitada->user_documents
        ->where('slug', 'justificante-presentacion-ayuda')
        ->first();
@endphp

<div class="cierre-container"
    style="
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    margin: 1.5rem 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    position: relative;
">

    <!-- Header con icono y título -->
    <div
        style="
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid {{ $esConcedida ? '#10b981' : ($esRechazada ? '#ef4444' : '#6b7280') }};
    ">
        <div
            style="
            background: linear-gradient(135deg, {{ $esConcedida ? '#10b981, #059669' : ($esRechazada ? '#ef4444, #dc2626' : '#6b7280, #4b5563') }});
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba({{ $esConcedida ? '16, 185, 129' : ($esRechazada ? '239, 68, 68' : '107, 114, 128') }}, 0.3);
        ">
            <span style="font-size: 2rem;">
                @if ($esConcedida)
                    🎉
                @elseif($esRechazada)
                    ❌
                @else
                    📋
                @endif
            </span>
        </div>
        <div>
            <h3
                style="
                color: #2d3748;
                font-size: 1.75rem;
                font-weight: 700;
                margin: 0;
            ">
                @if ($esConcedida)
                    ¡Enhorabuena!
                @elseif($esRechazada)
                    Solicitud Rechazada
                @else
                    Proceso Finalizado
                @endif
            </h3>
            <p
                style="
                color: {{ $esConcedida ? '#10b981' : ($esRechazada ? '#ef4444' : '#6b7280') }};
                font-size: 1.1rem;
                margin: 0.25rem 0 0 0;
                font-weight: 600;
            ">
                @if ($esConcedida)
                    Tu ayuda ha sido <strong>CONCEDIDA</strong>
                @elseif($esRechazada)
                    La solicitud ha sido <strong>RECHAZADA</strong>
                @else
                    Proceso <strong>FINALIZADO</strong>
                @endif
            </p>
        </div>
    </div>

    <!-- Mensaje principal -->
    <div
        style="
        background: {{ $esConcedida ? '#f0fdf4' : ($esRechazada ? '#fef2f2' : '#f9fafb') }};
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid {{ $esConcedida ? '#10b981' : ($esRechazada ? '#ef4444' : '#6b7280') }};
    ">
        @if ($esConcedida)
            <p
                style="
                color: #2d3748;
                font-size: 1.1rem;
                line-height: 1.6;
                margin: 0 0 1rem 0;
                text-align: center;
            ">
                🙏 <strong>Gracias por confiar en Tu Trámite Fácil.</strong>
            </p>
            <p
                style="
                color: #4a5568;
                font-size: 1rem;
                line-height: 1.6;
                margin: 0;
                text-align: center;
            ">
                Nos alegra haberte ayudado a conseguir este apoyo. En breve recibirás el ingreso en
                tu cuenta.
            </p>
        @elseif($esRechazada)
            <p
                style="
                color: #2d3748;
                font-size: 1.1rem;
                line-height: 1.6;
                margin: 0 0 1rem 0;
                text-align: center;
            ">
                😔 <strong>Lamentamos que tu solicitud haya sido rechazada.</strong>
            </p>
            <p
                style="
                color: #4a5568;
                font-size: 1rem;
                line-height: 1.6;
                margin: 0;
                text-align: center;
            ">
                Si crees que se trata de un error o quieres saber si puedes presentar alegaciones,
                contacta con nosotros y te orientaremos.
            </p>
        @else
            <p
                style="
                color: #2d3748;
                font-size: 1.1rem;
                line-height: 1.6;
                margin: 0 0 1rem 0;
                text-align: center;
            ">
                📋 <strong>Tu solicitud ha sido procesada.</strong>
            </p>
            <p
                style="
                color: #4a5568;
                font-size: 1rem;
                line-height: 1.6;
                margin: 0;
                text-align: center;
            ">
                Te contactaremos pronto con más información sobre el resultado.
            </p>
        @endif
    </div>

    <!-- Documento de resolución -->
    @if ($documentoResolucion)
        <div
            style="
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        ">
            <div
                style="
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1.5rem;
            ">
                <div
                    style="
                    background: {{ $esConcedida ? '#10b981' : ($esRechazada ? '#ef4444' : '#6b7280') }};
                    border-radius: 8px;
                    width: 50px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <span style="font-size: 1.5rem; color: white;">📄</span>
                </div>
                <div style="flex: 1;">
                    <h4
                        style="
                        color: #2d3748;
                        font-size: 1.2rem;
                        font-weight: 600;
                        margin: 0 0 0.5rem 0;
                    ">
                        @if ($esConcedida)
                            Resolución de Concesión
                        @elseif($esRechazada)
                            Resolución de Denegación
                        @else
                            Documento de Resolución
                        @endif
                    </h4>
                    <p
                        style="
                        color: #6b7280;
                        font-size: 0.9rem;
                        margin: 0;
                    ">
                        Documento oficial de la Administración
                    </p>
                </div>
            </div>

            <!-- Botones de acción -->
            <div
                style="
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            ">
                <a href="{{ $documentoResolucion->temporary_url }}" target="_blank"
                    style="
                       display: inline-flex;
                       align-items: center;
                       gap: 0.5rem;
                       padding: 0.75rem 1.5rem;
                       background: {{ $esConcedida ? '#10b981' : ($esRechazada ? '#ef4444' : '#6b7280') }};
                       color: white;
                       text-decoration: none;
                       border-radius: 6px;
                       font-weight: 600;
                       font-size: 0.95rem;
                       transition: all 0.2s ease;
                   "
                    onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                    Ver Documento
                </a>

                <a href="{{ $documentoResolucion->download_url }}"
                    style="
                       display: inline-flex;
                       align-items: center;
                       gap: 0.5rem;
                       padding: 0.75rem 1.5rem;
                       background: white;
                       color: {{ $esConcedida ? '#10b981' : ($esRechazada ? '#ef4444' : '#6b7280') }};
                       text-decoration: none;
                       border: 1px solid {{ $esConcedida ? '#10b981' : ($esRechazada ? '#ef4444' : '#6b7280') }};
                       border-radius: 6px;
                       font-weight: 600;
                       font-size: 0.95rem;
                       transition: all 0.2s ease;
                   "
                    onmouseover="this.style.background='#f0f9ff'"
                    onmouseout="this.style.background='white'">
                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Descargar
                </a>
            </div>
        </div>
    @else
        <div
            style="
            background: #fef3cd;
            border: 1px solid #feca57;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        ">
            <div
                style="
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                margin-bottom: 0.5rem;
            ">
                <span style="font-size: 1.3rem;">⚠️</span>
                <span
                    style="
                    color: #d69e2e;
                    font-weight: 600;
                    font-size: 1.1rem;
                ">Documento
                    pendiente</span>
            </div>
            <p
                style="
                color: #b7791f;
                font-size: 0.95rem;
                margin: 0;
                line-height: 1.5;
            ">
                El documento de resolución aún no está disponible. Se subirá automáticamente cuando
                esté listo.
            </p>
        </div>
    @endif

    <!-- Información adicional -->
    @if ($esRechazada)
        <div
            style="
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        ">
            <h6
                style="
                color: #dc2626;
                font-weight: 600;
                margin-bottom: 1rem;
            ">
                💡 ¿Qué puedes hacer ahora?
            </h6>
            <ul
                style="
                color: #7f1d1d;
                margin: 0;
                padding-left: 1.5rem;
            ">
                <li>Revisar los motivos del rechazo en el documento oficial</li>
                <li>Verificar si puedes presentar alegaciones</li>
                <li>Contactar con nuestro equipo para orientación</li>
                <li>Explorar otras ayudas disponibles</li>
            </ul>
        </div>
    @endif

</div>

<!-- Estilos adicionales para mejorar la responsividad -->
<style>
    @media (max-width: 768px) {
        .cierre-container {
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .cierre-container h3 {
            font-size: 1.5rem !important;
        }

        .cierre-container .botones-accion {
            flex-direction: column;
            align-items: stretch;
        }

        .cierre-container .botones-accion a {
            justify-content: center;
        }
    }
</style>
