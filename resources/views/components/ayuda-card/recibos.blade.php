@props(['ayudaSolicitada'])

@php
    use App\Helpers\SimulationHelper;
    use App\Models\Document;
    
    // Obtener el documento base de recibos para acceder a los campos informativos
    $docReciboBase = Document::where('id', 7)->first() 
        ?? Document::where('slug', 'justificantes-pago-alquiler')->first();
    
    $faltantes = collect($ayudaSolicitada->documentos_faltantes ?? []);
    $hayRecibosFaltantes = $faltantes->contains(fn($doc) => Str::contains($doc['name'], 'Recibo') || Str::contains($doc['slug'] ?? '', 'recibo'));
    $hayRecibosPendientes = collect($ayudaSolicitada->documentos_subidos ?? [])->contains(fn($doc, $slug) => Str::contains($slug, 'recibo') && ($doc['estado'] ?? null) === 'pendiente');
    
    // Recopilar todos los recibos
    $todosRecibos = collect();
    
    // Recibos faltantes
    foreach ($ayudaSolicitada->documentos_faltantes as $doc) {
        if (Str::contains($doc['name'], 'Recibo') || Str::contains($doc['slug'] ?? '', 'recibo')) {
            $slugConMes = $doc['slug'];
            $docReciboSubido = $ayudaSolicitada->recibos_subidos[$slugConMes]
                ?? \App\Models\UserDocument::where('user_id', SimulationHelper::getCurrentUserId())
                        ->where('slug', $slugConMes)
                        ->latest()
                        ->first();
            
            $estado = 'pending';
            if ($docReciboSubido) {
                if ($docReciboSubido->estado == 'validado') {
                    $estado = 'accepted';
                } elseif ($docReciboSubido->estado === 'pendiente') {
                    $estado = 'pending';
                } elseif ($docReciboSubido->estado === 'rechazado') {
                    $estado = 'rejected';
                }
            }
            
            if ($estado !== 'accepted' || !$docReciboSubido) {
                $todosRecibos->push([
                    'id' => $doc['id'],
                    'name' => $doc['name'],
                    'slug' => $slugConMes,
                    'estado' => $estado,
                    'doc' => $docReciboSubido,
                    'es_faltante' => true
                ]);
            }
        }
    }
    
    // Recibos pendientes o rechazados que no están en faltantes
    $recibosPendientesRevision = \App\Models\UserDocument::where('user_id', SimulationHelper::getCurrentUserId())
        ->whereIn('estado', ['pendiente', 'rechazado', 'validado'])
        ->get()
        ->filter(fn($doc) => Str::contains($doc->nombre_personalizado ?? $doc->slug, 'Recibo'))
        ->keyBy('slug');
    
    foreach ($recibosPendientesRevision as $slug => $docReciboSubido) {
        $esRecibo = Str::contains($docReciboSubido->nombre_personalizado ?? $slug, 'Recibo') || Str::contains($slug, 'recibo');
        $yaEstaEnFaltantes = $todosRecibos->contains(fn($r) => $r['slug'] === $slug);
        
        if ($esRecibo && !$yaEstaEnFaltantes) {
            $estado = 'pending';
            if ($docReciboSubido->estado === 'validado') {
                $estado = 'accepted';
            } elseif ($docReciboSubido->estado === 'rechazado') {
                $estado = 'rejected';
            } elseif ($docReciboSubido->estado === 'pendiente') {
                $estado = 'uploaded';
            }
            
            $todosRecibos->push([
                'id' => $docReciboSubido->id,
                'name' => $docReciboSubido->nombre_personalizado ?? ucfirst(str_replace('_', ' ', $slug)),
                'slug' => $slug,
                'estado' => $estado,
                'doc' => $docReciboSubido,
                'es_faltante' => false
            ]);
        }
    }
@endphp

@if ($hayRecibosFaltantes || $hayRecibosPendientes || $todosRecibos->isNotEmpty())
@php
    $recibosPendientes = $todosRecibos->filter(fn($r) => $r['estado'] === 'pending')->count();
@endphp
<div class="mb-4">
    {{-- Encabezado con título, descripción y botones de navegación --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="ttf-section-title mb-1">Recibos de alquiler</h2>
            <p class="text-sm text-muted-foreground">
                @if ($recibosPendientes > 0)
                    Tienes {{ $recibosPendientes }} {{ $recibosPendientes === 1 ? 'recibo pendiente' : 'recibos pendientes' }}
                @else
                    {{ $todosRecibos->count() }} {{ $todosRecibos->count() === 1 ? 'recibo' : 'recibos' }} en total
                @endif
            </p>
        </div>
        <div class="hidden sm:flex items-center gap-2">
            <button class="carousel-nav-btn carousel-nav-btn-left" 
                    aria-label="Desplazar izquierda"
                    data-direction="left"
                    data-target="reciboSlider-{{ $ayudaSolicitada->id }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
            </button>
            <button class="carousel-nav-btn carousel-nav-btn-right" 
                    aria-label="Desplazar derecha"
                    data-direction="right"
                    data-target="reciboSlider-{{ $ayudaSolicitada->id }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <div class="recibos-carousel-container">
        <div class="recibos-carousel flex gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2" id="reciboSlider-{{ $ayudaSolicitada->id }}">
            @foreach ($todosRecibos as $recibo)
                @php
                    $estado = $recibo['estado'];
                    $cardClasses = '';
                    $badgeClasses = '';
                    $badgeText = '';
                    $badgeIcon = '';
                    
                    if ($estado === 'pending') {
                        $cardClasses = 'border-warning/30 bg-warning-bg/30';
                        $badgeClasses = 'text-warning';
                        $badgeText = 'Pendiente';
                        $badgeIcon = 'triangle-alert';
                    } elseif ($estado === 'rejected') {
                        $cardClasses = 'border-destructive/30 bg-destructive/5';
                        $badgeClasses = 'text-destructive';
                        $badgeText = 'Rechazado';
                        $badgeIcon = 'triangle-alert';
                    } elseif ($estado === 'uploaded') {
                        $cardClasses = 'border-primary/30 bg-accent/50';
                        $badgeClasses = 'text-primary';
                        $badgeText = 'Subido';
                        $badgeIcon = 'clock';
                    } elseif ($estado === 'accepted') {
                        $cardClasses = 'border-primary/30 bg-accent/50';
                        $badgeClasses = 'text-primary';
                        $badgeText = 'Aceptado';
                        $badgeIcon = 'circle-check-big';
                    }
                    
                    // Extraer mes del nombre
                    $mes = '';
                    if (preg_match('/(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)/i', $recibo['name'], $matches)) {
                        $mes = ucfirst($matches[1]);
                    } elseif (preg_match('/(\d{4})/', $recibo['name'], $matches)) {
                        $mes = $matches[1];
                    }
                @endphp
                
                <article class="ttf-card min-w-[200px] sm:min-w-[220px] p-4 flex flex-col gap-3 snap-start transition-all duration-200 {{ $cardClasses }}" 
                         data-document-status="{{ $estado }}" 
                         data-document-id="recibo-{{ $recibo['slug'] }}">
                    
                    {{-- Badge de estado --}}
                    <div class="flex items-center gap-2">
                        <span class="ttf-warning-badge {{ $badgeClasses !== 'text-warning' ? 'bg-accent' : '' }}">
                            @if ($badgeIcon === 'triangle-alert')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-5 h-5 {{ $badgeClasses }}">
                                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                                    <path d="M12 9v4"></path>
                                    <path d="M12 17h.01"></path>
                                </svg>
                            @elseif ($badgeIcon === 'clock')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-5 h-5 {{ $badgeClasses }}">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            @elseif ($badgeIcon === 'circle-check-big')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big w-5 h-5 {{ $badgeClasses }}">
                                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                    <path d="m9 11 3 3L22 4"></path>
                                </svg>
                            @endif
                        </span>
                        <span class="text-xs font-medium uppercase tracking-wide {{ $estado === 'rejected' ? 'text-destructive' : 'text-muted-foreground' }}">
                            {{ $badgeText }}
                        </span>
                    </div>
                    
                    {{-- Título --}}
                    <h3 class="text-base font-semibold text-foreground leading-tight">
                        Recibo de alquiler<br>
                        <span class="text-primary">{{ $mes ?: $recibo['name'] }}</span>
                    </h3>
                    
                    {{-- Ver motivo (solo para rechazados) --}}
                    @if ($estado === 'rejected' && $recibo['doc'] && $recibo['doc']->motivo_rechazo)
                        <div class="text-xs">
                            <button class="flex items-center gap-1 text-destructive hover:text-destructive/80" 
                                    onclick="alert('{{ addslashes($recibo['doc']->motivo_rechazo) }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down w-3 h-3">
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                                <span>Ver motivo</span>
                            </button>
                        </div>
                    @endif
                    
                    {{-- Botones de acción --}}
                    <div class="flex flex-col gap-2 mt-auto">
                        @if ($estado === 'pending')
                            <button class="ttf-btn-primary w-full relative" 
                                    onclick="openModal({{ $recibo['id'] }}, '{{ $recibo['name'] }}', '{{ $recibo['slug'] }}', false, '', {{ $ayudaSolicitada->id }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload w-4 h-4">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" x2="12" y1="3" y2="15"></line>
                                </svg>
                                Subir ahora
                            </button>
                            @if ($docReciboBase && !empty($docReciboBase->informative_clickable_text))
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick="window.showInformativeDocSidebar(
                                            {{ json_encode($docReciboBase->informative_header_text ?? '') }},
                                            {{ json_encode($docReciboBase->informative_clickable_text ?? '') }},
                                            {{ json_encode($docReciboBase->informative_link ?? '') }},
                                            {{ json_encode($docReciboBase->informative_link_text ?? '') }},
                                            {{ $recibo['id'] }},
                                            {{ json_encode($recibo['name']) }},
                                            {{ json_encode($recibo['slug']) }},
                                            false
                                        )">
                                    <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
                                </button>
                            @endif
                        @elseif ($estado === 'rejected')
                            <button class="ttf-btn-primary w-full relative" 
                                    onclick="openModal({{ $recibo['id'] }}, '{{ $recibo['name'] }}', '{{ $recibo['slug'] }}', false, '', {{ $ayudaSolicitada->id }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload w-4 h-4">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" x2="12" y1="3" y2="15"></line>
                                </svg>
                                Volver a subir
                            </button>
                            @if ($docReciboBase && !empty($docReciboBase->informative_clickable_text))
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick="window.showInformativeDocSidebar(
                                            {{ json_encode($docReciboBase->informative_header_text ?? '') }},
                                            {{ json_encode($docReciboBase->informative_clickable_text ?? '') }},
                                            {{ json_encode($docReciboBase->informative_link ?? '') }},
                                            {{ json_encode($docReciboBase->informative_link_text ?? '') }},
                                            {{ $recibo['id'] }},
                                            {{ json_encode($recibo['name']) }},
                                            {{ json_encode($recibo['slug']) }},
                                            false
                                        )">
                                    <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
                                </button>
                            @endif
                        @else
                            {{-- Subido o Aceptado - sin botones --}}
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>

<style>
    .recibos-carousel-container {
        margin: 0 -1rem;
        padding: 0 1rem;
    }
    
    @media (min-width: 640px) {
        .recibos-carousel-container {
            margin: 0;
            padding: 0;
        }
    }
    
    .recibos-carousel {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .recibos-carousel::-webkit-scrollbar {
        display: none;
    }
    
    .hide-scrollbar {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    
    .snap-x {
        scroll-snap-type: x mandatory;
    }
    
    .snap-mandatory {
        scroll-snap-type: x mandatory;
    }
    
    .snap-start {
        scroll-snap-align: start;
    }
    
    .ttf-card {
        border-radius: 0.75rem;
        border-width: 1px;
        border-color: hsl(var(--border));
        background-color: hsl(var(--card));
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }
    
    .border-warning\/30 {
        border-color: rgba(250, 204, 21, 0.3);
    }
    
    .bg-warning-bg\/30 {
        background-color: rgba(254, 243, 199, 0.3);
    }
    
    .border-destructive\/30 {
        border-color: rgba(239, 68, 68, 0.3);
    }
    
    .bg-destructive\/5 {
        background-color: rgba(239, 68, 68, 0.05);
    }
    
    .border-primary\/30 {
        border-color: rgba(84, 222, 189, 0.3);
    }
    
    .bg-accent\/50 {
        background-color: rgba(84, 222, 189, 0.1);
    }
    
    .text-warning {
        color: #facc15;
    }
    
    .text-destructive {
        color: #ef4444;
    }
    
    .text-primary {
        color: #54debd;
    }
    
    .text-muted-foreground {
        color: #6b7280;
    }
    
    .text-foreground {
        color: #1f2937;
    }
    
    .ttf-warning-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .ttf-warning-badge.bg-accent {
        background-color: rgba(84, 222, 189, 0.2);
        border-radius: 50%;
        padding: 4px;
    }
    
    .ttf-btn-primary {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1rem;
        background: linear-gradient(135deg, #54debd 0%, #40d4b0 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(84, 222, 189, 0.2);
    }
    
    .ttf-btn-primary:hover {
        background: linear-gradient(135deg, #40d4b0 0%, #3c3a60 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(84, 222, 189, 0.3);
        color: white;
    }
    
    .ttf-btn-secondary {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: white;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .ttf-btn-secondary:hover {
        background: #f9fafb;
        border-color: #d1d5db;
        color: #1f2937;
    }
    
    .gap-2 {
        gap: 0.5rem;
    }
    
    .gap-3 {
        gap: 0.75rem;
    }
    
    .gap-4 {
        gap: 1rem;
    }
    
    .flex {
        display: flex;
    }
    
    .flex-col {
        flex-direction: column;
    }
    
    .items-center {
        align-items: center;
    }
    
    .mt-auto {
        margin-top: auto;
    }
    
    .w-full {
        width: 100%;
    }
    
    .p-4 {
        padding: 1rem;
    }
    
    .min-w-\[200px\] {
        min-width: 200px;
    }
    
    @media (min-width: 640px) {
        .sm\:min-w-\[220px\] {
            min-width: 220px;
        }
    }
    
    .overflow-x-auto {
        overflow-x: auto;
    }
    
    .pb-2 {
        padding-bottom: 0.5rem;
    }
    
    .text-xs {
        font-size: 0.75rem;
        line-height: 1rem;
    }
    
    .text-base {
        font-size: 1rem;
        line-height: 1.5rem;
    }
    
    .font-medium {
        font-weight: 500;
    }
    
    .font-semibold {
        font-weight: 600;
    }
    
    .uppercase {
        text-transform: uppercase;
    }
    
    .tracking-wide {
        letter-spacing: 0.025em;
    }
    
    .leading-tight {
        line-height: 1.25;
    }
    
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 0.15s;
    }
    
    .duration-200 {
        transition-duration: 0.2s;
    }
    
    .w-3 {
        width: 0.75rem;
    }
    
    .w-3\.5 {
        width: 0.875rem;
    }
    
    .w-4 {
        width: 1rem;
    }
    
    .w-5 {
        width: 1.25rem;
    }
    
    .h-3 {
        height: 0.75rem;
    }
    
    .h-3\.5 {
        height: 0.875rem;
    }
    
    .h-4 {
        height: 1rem;
    }
    
    .h-5 {
        height: 1.25rem;
    }
    
    /* Estilos para el encabezado */
    .ttf-section-title {
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.75rem;
        color: #1f2937;
    }
    
    .justify-between {
        justify-content: space-between;
    }
    
    .mb-1 {
        margin-bottom: 0.25rem;
    }
    
    /* Estilos mejorados para botones de navegación */
    .carousel-nav-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        padding: 0;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        color: #54debd;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .carousel-nav-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #54debd 0%, #40d4b0 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .carousel-nav-btn svg {
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease, color 0.3s ease;
    }
    
    .carousel-nav-btn:hover {
        background: linear-gradient(135deg, #54debd 0%, #40d4b0 100%);
        border-color: #54debd;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(84, 222, 189, 0.3), 0 2px 4px rgba(84, 222, 189, 0.2);
    }
    
    .carousel-nav-btn:hover::before {
        opacity: 1;
    }
    
    .carousel-nav-btn:hover svg {
        color: #ffffff;
        transform: scale(1.1);
    }
    
    .carousel-nav-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(84, 222, 189, 0.2);
    }
    
    .carousel-nav-btn:focus {
        outline: none;
        ring: 2px solid rgba(84, 222, 189, 0.3);
        ring-offset: 2px;
    }
    
    @media (min-width: 640px) {
        .sm\:flex {
            display: flex;
        }
    }
    
    .hidden {
        display: none;
    }
</style>

<script>
// Función para desplazar el carrusel
function scrollCarousel(direction, carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const cardWidth = 220; // Ancho aproximado de cada tarjeta + gap
    const scrollAmount = cardWidth;
    
    if (direction === 'left') {
        carousel.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    } else {
        carousel.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
}

// Usar delegación de eventos para que funcione con contenido dinámico
document.addEventListener('click', function(e) {
    // Verificar si el clic fue en un botón de navegación del carrusel
    const button = e.target.closest('.carousel-nav-btn');
    if (button) {
        const direction = button.getAttribute('data-direction');
        const target = button.getAttribute('data-target');
        if (direction && target) {
            e.preventDefault();
            scrollCarousel(direction, target);
        }
    }
});
</script>
@endif
