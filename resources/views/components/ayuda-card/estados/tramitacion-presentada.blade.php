@php
    // Obtener el documento de presentación si existe
    $documentoPresentacion = $ayudaSolicitada->user_documents
        ->where('slug', 'justificante-presentacion-ayuda')
        ->first();
@endphp

<div class="tramitacion-presentada-container" style="
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    margin: 1.5rem 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    position: relative;
    border-left: 6px solid #3b82f6;
">
    
    <!-- Header con icono y título -->
    <div style="
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid #3b82f6;
    ">
        <div style="
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        ">
            <span style="font-size: 2rem;">📤</span>
        </div>
        <div>
            <h3 style="
                color: #2d3748;
                font-size: 1.75rem;
                font-weight: 700;
                margin: 0;
            ">Solicitud Presentada</h3>
            <p style="
                color: #3b82f6;
                font-size: 1.1rem;
                margin: 0.25rem 0 0 0;
                font-weight: 600;
            ">Tu solicitud ha sido <strong>ENVIADA</strong> a la Administración</p>
        </div>
    </div>

    <!-- Mensaje principal -->
    <div style="
        background: #f0f9ff;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #3b82f6;
    ">
        <p style="
            color: #2d3748;
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0 0 1rem 0;
            text-align: center;
        ">
            ✅ <strong>¡Excelente! Tu solicitud ha sido presentada correctamente.</strong>
        </p>
        <p style="
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
            text-align: center;
        ">
            Ahora esperamos la resolución por parte de la Administración. Te avisaremos en cuanto haya novedades.
        </p>
    </div>

    <!-- Documento de presentación -->
    @if($documentoPresentacion)
        <div style="
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        ">
            <div style="
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1.5rem;
            ">
                <div style="
                    background: #3b82f6;
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
                    <h4 style="
                        color: #2d3748;
                        font-size: 1.2rem;
                        font-weight: 600;
                        margin: 0 0 0.5rem 0;
                    ">Justificante de Presentación</h4>
                    <p style="
                        color: #6b7280;
                        font-size: 0.9rem;
                        margin: 0;
                    ">
                        Documento que confirma que tu solicitud ha sido presentada
                    </p>
                </div>
            </div>

            <!-- Botones de acción -->
            <div style="
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            ">
                <a href="{{ $documentoPresentacion->temporary_url }}" 
                   target="_blank" 
                   style="
                       display: inline-flex;
                       align-items: center;
                       gap: 0.5rem;
                       padding: 0.75rem 1.5rem;
                       background: #3b82f6;
                       color: white;
                       text-decoration: none;
                       border-radius: 6px;
                       font-weight: 600;
                       font-size: 0.95rem;
                       transition: all 0.2s ease;
                   "
                   onmouseover="this.style.opacity='0.9'"
                   onmouseout="this.style.opacity='1'">
                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Ver Documento
                </a>
                
                <a href="{{ $documentoPresentacion->download_url }}" 
                   style="
                       display: inline-flex;
                       align-items: center;
                       gap: 0.5rem;
                       padding: 0.75rem 1.5rem;
                       background: white;
                       color: #3b82f6;
                       text-decoration: none;
                       border: 1px solid #3b82f6;
                       border-radius: 6px;
                       font-weight: 600;
                       font-size: 0.95rem;
                       transition: all 0.2s ease;
                   "
                   onmouseover="this.style.background='#f0f9ff'"
                   onmouseout="this.style.background='white'">
                    <svg style="width: 1.1rem; height: 1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Descargar
                </a>
            </div>
        </div>
    @else
        <div style="
            background: #fef3cd;
            border: 1px solid #feca57;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        ">
            <div style="
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                margin-bottom: 0.5rem;
            ">
                <span style="font-size: 1.3rem;">⚠️</span>
                <span style="
                    color: #d69e2e;
                    font-weight: 600;
                    font-size: 1.1rem;
                ">Documento pendiente</span>
            </div>
            <p style="
                color: #b7791f;
                font-size: 0.95rem;
                margin: 0;
                line-height: 1.5;
            ">
                El justificante de presentación aún no está disponible. Se subirá automáticamente cuando esté listo.
            </p>
        </div>
    @endif

    <!-- Tabs para documentos, datos y subsanación -->
    <ul class="nav nav-tabs mt-4" id="tab-{{ $ayudaSolicitada->id }}" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="docs-tab-{{ $ayudaSolicitada->id }}" data-bs-toggle="tab"
                data-bs-target="#docs-{{ $ayudaSolicitada->id }}" type="button" role="tab"
                aria-controls="docs-{{ $ayudaSolicitada->id }}" aria-selected="true">📄 Documentos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="datos-tab-{{ $ayudaSolicitada->id }}" data-bs-toggle="tab"
                data-bs-target="#datos-{{ $ayudaSolicitada->id }}" type="button" role="tab"
                aria-controls="datos-{{ $ayudaSolicitada->id }}" aria-selected="false">📝 Datos</button>
        </li>
        @if($ayudaSolicitada->motivosSubsanacionContrataciones && $ayudaSolicitada->motivosSubsanacionContrataciones->count() > 0)
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="subsanacion-tab-{{ $ayudaSolicitada->id }}" data-bs-toggle="tab"
                data-bs-target="#subsanacion-{{ $ayudaSolicitada->id }}" type="button" role="tab"
                aria-controls="subsanacion-{{ $ayudaSolicitada->id }}" aria-selected="false">⚠️ Subsanación</button>
        </li>
        @endif
    </ul>
    <div class="tab-content mt-3" id="tabContent-{{ $ayudaSolicitada->id }}">
        <div class="tab-pane fade show active" id="docs-{{ $ayudaSolicitada->id }}" role="tabpanel"
            aria-labelledby="docs-tab-{{ $ayudaSolicitada->id }}">
            <x-ayuda-card.documentos :ayudaSolicitada="$ayudaSolicitada" />
        </div>
        <div class="tab-pane fade" id="datos-{{ $ayudaSolicitada->id }}" role="tabpanel"
            aria-labelledby="datos-tab-{{ $ayudaSolicitada->id }}">
            <div class="alert alert-info">
                <h6 class="alert-heading">📋 Información de contacto</h6>
                <p class="mb-0">Si necesitas modificar algún dato, contacta con nuestro equipo de soporte.</p>
            </div>
        </div>
        
        @if($ayudaSolicitada->motivosSubsanacionContrataciones && $ayudaSolicitada->motivosSubsanacionContrataciones->count() > 0)
        <div class="tab-pane fade" id="subsanacion-{{ $ayudaSolicitada->id }}" role="tabpanel"
            aria-labelledby="subsanacion-tab-{{ $ayudaSolicitada->id }}">
            <div class="alert alert-warning">
                <h6 class="alert-heading">⚠️ Documentos de Subsanación Requeridos</h6>
                <p class="mb-0">Se requieren los siguientes documentos para completar tu solicitud:</p>
            </div>
            
            <ul class="list-unstyled mt-2">
                @foreach($ayudaSolicitada->motivosSubsanacionContrataciones as $motivoContratacion)
                    @if($motivoContratacion->motivo && $motivoContratacion->motivo->document)
                        @php
                            // Buscar si ya existe un documento subido para este tipo
                            $documentoSubido = $ayudaSolicitada->user_documents
                                ->where('document_id', $motivoContratacion->motivo->document_id)
                                ->first();
                            
                            $backgroundClass = 'bg-white';
                            if ($documentoSubido) {
                                if ($documentoSubido->estado === 'pendiente') {
                                    $backgroundClass = 'bg-warning-subtle';
                                } elseif ($documentoSubido->estado === 'rechazado') {
                                    $backgroundClass = 'bg-danger-subtle';
                                }
                            }
                        @endphp
                        
                        <li class="border rounded p-3 mb-2 shadow-sm {{ $backgroundClass }}">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                                {{-- Icono + nombre --}}
                                <div class="d-flex align-items-center gap-2 text-start w-100 w-md-auto">
                                    @if($documentoSubido)
                                        @if($documentoSubido->estado === 'pendiente')
                                            <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                                                 alt="En revisión" style="width: 24px; height: 24px;">
                                        @elseif($documentoSubido->estado === 'rechazado')
                                            <img src="{{ asset('imagenes/32px-Cross_red_circle.svg.png') }}"
                                                 alt="Rechazado" style="width: 24px; height: 24px;">
                                        @else
                                            <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                                                 alt="Pendiente" style="width: 24px; height: 24px;">
                                        @endif
                                    @else
                                        <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                                             alt="Falta documento" style="width: 24px; height: 24px;">
                                    @endif
                                    
                                    <div>
                                        <span class="fw-semibold text-dark">{{ $motivoContratacion->motivo->document->name }}</span>
                                        @if($motivoContratacion->nota)
                                            <div class="mt-2 p-2 bg-light rounded border-start border-3 border-info">
                                                <small class="text-muted d-block mb-1">
                                                    <i class="bx bx-note me-1"></i>Nota adicional:
                                                </small>
                                                <div class="text-dark small">{{ $motivoContratacion->nota }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Estado + botón --}}
                                <div class="d-flex flex-column align-items-center align-items-md-end text-center text-md-end w-100 w-md-auto">
                                    @if($documentoSubido)
                                        @if($documentoSubido->estado === 'pendiente')
                                            <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
                                        @elseif($documentoSubido->estado === 'rechazado')
                                            <div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>
                                            <button class="btn btn-sm btn-warning mt-2 btn-secondary"
                                                onclick="openModal({{ $motivoContratacion->motivo->document_id }}, '{{ $motivoContratacion->motivo->document->name }}', '{{ $motivoContratacion->motivo->document->slug }}', false, '', {{ $ayudaSolicitada->id }}, true, {{ $motivoContratacion->motivo->id }})">
                                                Reintentar subida
                                            </button>
                                        @else
                                            <div class="text-success medium">✅ Documento subido correctamente</div>
                                        @endif
                                    @else
                                        <button class="btn btn-sm btn-primary"
                                            onclick="openModal({{ $motivoContratacion->motivo->document_id }}, '{{ $motivoContratacion->motivo->document->name }}', '{{ $motivoContratacion->motivo->document->slug }}', false, '', {{ $ayudaSolicitada->id }}, true, {{ $motivoContratacion->motivo->id }})">
                                            Subir ahora
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        @endif
    </div>

</div>

<!-- Estilos adicionales para mejorar la responsividad -->
<style>
    @media (max-width: 768px) {
        .tramitacion-presentada-container {
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .tramitacion-presentada-container h3 {
            font-size: 1.5rem !important;
        }
        
        .tramitacion-presentada-container .botones-accion {
            flex-direction: column;
            align-items: stretch;
        }
        
        .tramitacion-presentada-container .botones-accion a {
            justify-content: center;
        }
    }
</style>
