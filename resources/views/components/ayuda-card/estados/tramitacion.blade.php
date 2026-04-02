@php
    $fase = $ayudaSolicitada->fase ?? 'en_seguimiento';
@endphp

<div class="border-top pt-4 mt-4" style="border-color: #e5e7eb;">
    <!-- Header del estado -->
    <div class="d-flex align-items-center mb-4">
        <div class="me-3">
            <div style="
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                border-radius: 50%;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            ">
                <span style="font-size: 1.5rem; color: white;">⚙️</span>
            </div>
        </div>
        <div>
            <h4 class="mb-1" style="color: #1e40af; font-weight: 700;">
                @switch($fase)
                    @case('en_seguimiento')
                        En Seguimiento
                        @break
                    @case('apertura')
                        Preparando Solicitud
                        @break
                    @case('presentada')
                        Solicitud Presentada
                        @break
                    @default
                        En Tramitación
                @endswitch
            </h4>
            <p class="text-muted mb-0">
                @switch($fase)
                    @case('en_seguimiento')
                        Nuestro equipo está revisando tu solicitud
                        @break
                    @case('apertura')
                        Preparando tu solicitud para presentarla a la Administración
                        @break
                    @case('presentada')
                        Tu solicitud ha sido presentada a la Administración
                        @break
                    @default
                        Procesando tu solicitud
                @endswitch
            </p>
        </div>
    </div>

    <!-- Barra de progreso de la fase -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-sm text-muted">Progreso de tramitación</span>
            <span class="text-sm fw-bold text-primary">
                @switch($fase)
                    @case('en_seguimiento')
                        25%
                        @break
                    @case('apertura')
                        50%
                        @break
                    @case('presentada')
                        75%
                        @break
                    @default
                        25%
                @endswitch
            </span>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 
                @switch($fase)
                    @case('en_seguimiento')
                        25%
                        @break
                    @case('apertura')
                        50%
                        @break
                    @case('presentada')
                        75%
                        @break
                    @default
                        25%
                @endswitch
            "></div>
        </div>
    </div>

    <!-- Información específica de la fase -->
    <div class="alert alert-info border-0 mb-4" style="background-color: #f0f9ff;">
        <div class="d-flex align-items-start">
            <div class="me-3">
                @switch($fase)
                    @case('en_seguimiento')
                        <span style="font-size: 1.5rem;">🔍</span>
                        @break
                    @case('apertura')
                        <span style="font-size: 1.5rem;">📋</span>
                        @break
                    @case('presentada')
                        <span style="font-size: 1.5rem;">📤</span>
                        @break
                    @default
                        <span style="font-size: 1.5rem;">⚙️</span>
                @endswitch
            </div>
            <div>
                <h6 class="alert-heading mb-2">
                    @switch($fase)
                        @case('en_seguimiento')
                            Revisión en curso
                            @break
                        @case('apertura')
                            Preparando documentación
                            @break
                        @case('presentada')
                            Solicitud enviada
                            @break
                        @default
                            Procesando
                    @endswitch
                </h6>
                <p class="mb-0">
                    @switch($fase)
                        @case('en_seguimiento')
                            Estamos revisando todos los documentos y datos que has proporcionado. Te avisaremos si necesitamos algo más.
                            @break
                        @case('apertura')
                            Estamos preparando tu solicitud para presentarla a la Administración. Este proceso puede tardar unos días.
                            @break
                        @case('presentada')
                            Tu solicitud ha sido presentada correctamente a la Administración. Ahora esperamos su resolución.
                            @break
                        @default
                            Tu solicitud está siendo procesada por nuestro equipo.
                    @endswitch
                </p>
            </div>
        </div>
    </div>

    <!-- Tabs para documentos y datos (solo en fase en_seguimiento) -->
    @if($fase === 'en_seguimiento')
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
        </div>
    @else
        <!-- Para otras fases, mostrar documentos pendientes y información -->
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
        </div>
    @endif
</div>
