@props(['ayudaSolicitada'])

<style>
    .ayuda-card-modern {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
    }

    .ayuda-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.08);
        border-color: #54debd;
    }

    .ayuda-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #54debd 0%, #40d4b0 100%);
    }

    .organo-image-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 50%;
        border: 3px solid #54debd;
        box-shadow: 0 4px 12px rgba(84, 222, 189, 0.2);
        margin: 0 auto 1rem;
        padding: 8px;
    }

    .organo-image-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }

    .ayuda-title-modern {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
        margin-bottom: 1rem;
        text-align: center;
    }

    .descripcion-box {
        background: linear-gradient(135deg, rgba(207, 250, 254, 0.3) 0%, rgba(186, 230, 253, 0.2) 100%);
        border-left: 4px solid #54debd;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .progress-container {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
    }

    .progress-modern {
        height: 12px;
        background-color: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .progress-bar-modern {
        height: 100%;
        background: linear-gradient(90deg, #54debd 0%, #40d4b0 100%);
        border-radius: 10px;
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(84, 222, 189, 0.4);
        position: relative;
        overflow: hidden;
    }

    .progress-bar-modern::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .estado-badge {
        display: inline-block;
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-ver-detalles {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #54debd 0%, #40d4b0 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(84, 222, 189, 0.3);
    }

    .btn-ver-detalles:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(84, 222, 189, 0.4);
        background: linear-gradient(135deg, #40d4b0 0%, #3c3a60 100%);
    }

    .porcentaje-text {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 0.5rem;
    }
</style>

<div class="ayuda-card-wrapper mb-4">
    <a href="{{ route('user.AyudasSolicitadas.show', $ayudaSolicitada->id) }}"
        class="ayuda-card-modern text-decoration-none text-dark d-block p-4">

        {{-- Header con imagen y título --}}
        <div class="d-flex flex-column align-items-center mb-4">
            <div class="organo-image-container">
                <img src="{{ asset('imagenes/organos/' . $ayudaSolicitada->ayuda->organo->imagen) }}"
                    alt="{{ $ayudaSolicitada->ayuda->organo->nombre_organismo }}" />
            </div>
            <h5 class="ayuda-title-modern">
                {{ $ayudaSolicitada->ayuda->nombre_ayuda }}
            </h5>
        </div>

        {{-- Descripción --}}
        @if ($ayudaSolicitada->ayuda->description)
            <div class="descripcion-box">
                <span class="font-weight-semibold text-primary mb-2 d-block">📋 Descripción:</span>
                <p class="text-gray-700 mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                    {!! Str::limit(strip_tags($ayudaSolicitada->ayuda->description), 180, '...') !!}
                </p>
            </div>
        @endif

        {{-- Barra de progreso y estado (según estados OPx de contratacion_estado_contratacion) --}}
        @php
            $displayData = $ayudaSolicitada->getAyudaCardDisplayData();
            $porcentaje = $displayData['porcentaje'];
        @endphp
        <div class="progress-container">
            <div class="progress-modern">
                <div class="progress-bar-modern" role="progressbar"
                    style="width: {{ $porcentaje }}%;">
                </div>
            </div>
            <div class="text-center porcentaje-text">
                {{ $porcentaje }}% completado
            </div>
        </div>

        {{-- Estado --}}
        <div class="text-center mb-3">
            <span class="estado-badge {{ $displayData['badge_classes_bs'] }}">
                {{ $displayData['label'] }}
            </span>
        </div>

        {{-- Botón ver detalles --}}
        <div class="d-flex justify-content-center">
            <span class="btn-ver-detalles">
                Ver detalles
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"></path>
                    <path d="m12 5 7 7-7 7"></path>
                </svg>
            </span>
        </div>
    </a>
</div>
