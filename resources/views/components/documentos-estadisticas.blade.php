@props(['ayudaSolicitada'])

@php
    use App\Helpers\SimulationHelper;
    use App\Models\UserDocument;
    use App\Models\AyudaDocumentoConviviente;
    use App\Models\Conviviente;
    use Illuminate\Support\Str;
    
    $userId = SimulationHelper::getCurrentUserId();
    $ayudaId = $ayudaSolicitada->ayuda->id ?? $ayudaSolicitada->ayuda_id;
    $ayudaSolicitadaId = $ayudaSolicitada->id ?? $ayudaSolicitada->ayuda_id ?? null;
    
    // Contar documentos del SOLICITANTE directamente desde la BD
    // Los documentos con estado='pendiente' ya están SUBIDOS (esperando validación del equipo)
    // NO deben contarse como "pendientes de subir", sino como "subidos" o "en revisión"
    $docsValidadosBD = UserDocument::where('user_id', $userId)
        ->whereNull('conviviente_index')
        ->where('estado', 'validado')
        ->count();
    
    $docsEnRevisionBD = UserDocument::where('user_id', $userId)
        ->whereNull('conviviente_index')
        ->where('estado', 'pendiente')
        ->count();
    
    $docsRechazadosBD = UserDocument::where('user_id', $userId)
        ->whereNull('conviviente_index')
        ->where('estado', 'rechazado')
        ->count();
    
    // Contar documentos de CONVIVIENTES desde la BD
    $docsConvivientesValidadosBD = UserDocument::where('user_id', $userId)
        ->whereNotNull('conviviente_index')
        ->where('estado', 'validado')
        ->count();
    
    $docsConvivientesEnRevisionBD = UserDocument::where('user_id', $userId)
        ->whereNotNull('conviviente_index')
        ->where('estado', 'pendiente')
        ->count();
    
    $docsConvivientesRechazadosBD = UserDocument::where('user_id', $userId)
        ->whereNotNull('conviviente_index')
        ->where('estado', 'rechazado')
        ->count();
    
    // Obtener documentos de convivientes necesarios para esta ayuda
    $documentosConvivientesNecesarios = AyudaDocumentoConviviente::with('documento')
        ->where('ayuda_id', $ayudaId)
        ->get();
    
    // Obtener convivientes del usuario
    $convivientes = Conviviente::where('user_id', $userId)
        ->orderBy('index')
        ->get();
    
    // Calcular total de documentos de convivientes necesarios
    // Cada documento necesario se multiplica por el número de convivientes
    $totalDocsConvivientesNecesarios = $documentosConvivientesNecesarios->count() * $convivientes->count();
    
    // Contar documentos desde documentos_subidos también
    // Los documentos con estado='pendiente' ya están SUBIDOS (esperando validación)
    // NO deben contarse como "pendientes de subir"
    $documentosSubidos = collect($ayudaSolicitada->documentos_subidos ?? []);
    $completadosSubidos = 0;
    $enRevisionSubidos = 0; // Documentos subidos pero en revisión (estado='pendiente')
    $rechazadosSubidos = 0;
    
    foreach ($documentosSubidos as $slug => $doc) {
        // Excluir recibos de documentos_subidos (se cuentan por separado)
        if (Str::contains($slug, 'recibo')) {
            continue;
        }
        
        if (isset($doc['estado']) && $doc['estado'] === 'validado') {
            $completadosSubidos++;
        } elseif (isset($doc['estado']) && $doc['estado'] === 'pendiente') {
            // Estado 'pendiente' = ya subido, esperando validación (NO es pendiente de subir)
            $enRevisionSubidos++;
        } elseif (isset($doc['estado']) && $doc['estado'] === 'rechazado') {
            $rechazadosSubidos++;
        }
    }
    
    // Contar recibos por separado
    $recibosSubidos = collect($ayudaSolicitada->recibos_subidos ?? []);
    $recibosCompletados = 0;
    $recibosEnRevision = 0; // Recibos subidos pero en revisión (estado='pendiente')
    $recibosRechazados = 0;
    
    foreach ($recibosSubidos as $slug => $recibo) {
        if (isset($recibo['estado'])) {
            if ($recibo['estado'] === 'validado') {
                $recibosCompletados++;
            } elseif ($recibo['estado'] === 'pendiente') {
                // Estado 'pendiente' = ya subido, esperando validación (NO es pendiente de subir)
                $recibosEnRevision++;
            } elseif ($recibo['estado'] === 'rechazado') {
                $recibosRechazados++;
            }
        }
    }
    
    // Contar recibos faltantes (que no están en recibos_subidos)
    $recibosFaltantes = collect($ayudaSolicitada->documentos_faltantes ?? [])
        ->filter(fn($doc) => Str::contains($doc['name'] ?? '', 'Recibo') || Str::contains($doc['slug'] ?? '', 'recibo'))
        ->count();
    
    // Usar los valores más altos (puede haber documentos no en documentos_subidos)
    // Documentos del SOLICITANTE
    // Los documentos "en revisión" (estado='pendiente') se cuentan como subidos, no como pendientes de subir
    $completadosSolicitante = max($completadosSubidos, $docsValidadosBD);
    $enRevisionSolicitante = max($enRevisionSubidos, $docsEnRevisionBD); // Ya subidos, esperando validación
    $rechazadosSolicitante = max($rechazadosSubidos, $docsRechazadosBD);
    
    // Documentos de CONVIVIENTES
    $completadosConvivientes = $docsConvivientesValidadosBD;
    $enRevisionConvivientes = $docsConvivientesEnRevisionBD; // Ya subidos, esperando validación
    $rechazadosConvivientes = $docsConvivientesRechazadosBD;
    
    // Totales incluyendo recibos
    // Los documentos "en revisión" se cuentan como subidos (no como pendientes de subir)
    $completados = $completadosSolicitante + $completadosConvivientes + $recibosCompletados;
    $enRevision = $enRevisionSolicitante + $enRevisionConvivientes + $recibosEnRevision; // Ya subidos
    $rechazados = $rechazadosSolicitante + $rechazadosConvivientes + $recibosRechazados;
    
    // Total de documentos requeridos del SOLICITANTE (solo faltantes, sin configurados)
    // Excluir recibos de documentos_faltantes ya que se cuentan por separado
    $faltantesSolicitante = collect($ayudaSolicitada->documentos_faltantes ?? [])
        ->filter(fn($doc) => !Str::contains($doc['name'] ?? '', 'Recibo') && !Str::contains($doc['slug'] ?? '', 'recibo'))
        ->count();
    
    // Documentos faltantes de convivientes (necesarios pero no subidos)
    // Los documentos "en revisión" ya están subidos, así que no se cuentan como faltantes
    $docsConvivientesFaltantes = $totalDocsConvivientesNecesarios - ($completadosConvivientes + $enRevisionConvivientes + $rechazadosConvivientes);
    $docsConvivientesFaltantes = max(0, $docsConvivientesFaltantes);
    
    // Los documentos faltantes son los que realmente están pendientes de subir
    // NO incluir documentos "en revisión" (estado='pendiente') porque ya están subidos
    $pendientes = $faltantesSolicitante + $recibosFaltantes + $docsConvivientesFaltantes;
    
    // Total de documentos: solicitante + convivientes + recibos (sin configurados)
    // Los documentos "en revisión" se cuentan como subidos
    $totalRecibos = $recibosCompletados + $recibosEnRevision + $recibosRechazados + $recibosFaltantes;
    $totalDocumentosSolicitante = $faltantesSolicitante + $completadosSolicitante + $enRevisionSolicitante + $rechazadosSolicitante;
    $totalDocumentosConvivientes = $totalDocsConvivientesNecesarios;
    $totalDocumentos = $totalDocumentosSolicitante + $totalDocumentosConvivientes + $totalRecibos;
    
    // Asegurar que el total sea al menos la suma de completados + en revisión + rechazados + pendientes
    $totalDocumentos = max($totalDocumentos, $completados + $enRevision + $rechazados + $pendientes);
    
    // Calcular tiempo estimado (máximo 15 minutos, disminuye cuando hay menos documentos pendientes)
    // Lógica: 
    // - Si no hay documentos subidos todos faltantes tiempo máximo 15 min
    // - A medida que se completan documentos tiempo disminuye
    // - Si todos están completados  tiempo mínimo (2 min)
    $tiempoMaximo = 15;
    $tiempoMinimo = 2;
    
    // Calcular documentos restantes (solo los que faltan por subir, NO los en revisión)
    // Los documentos "en revisión" ya están subidos, así que no cuentan como restantes
    $documentosRestantes = $pendientes; // Solo los faltantes (aún no subidos)
    
    // Documentos ya subidos (completados + en revisión)
    $documentosSubidos = $completados + $enRevision;
    
    if ($documentosSubidos == 0 && $documentosRestantes > 0) {
        // Si no hay documentos subidos, tiempo máximo
        $tiempoEstimado = $tiempoMaximo;
    } elseif ($documentosRestantes == 0) {
        // Si no hay documentos pendientes de subir, tiempo mínimo
        $tiempoEstimado = $tiempoMinimo;
    } else {
        // Calcular tiempo proporcional basado en porcentaje de documentos subidos
        // Más documentos subidos = menos tiempo
        // Menos documentos subidos = más tiempo
        $porcentajeSubido = $totalDocumentos > 0 ? ($documentosSubidos / $totalDocumentos) : 0;
        
        // El tiempo disminuye a medida que aumenta el porcentaje subido
        $tiempoEstimado = $tiempoMaximo - ($porcentajeSubido * ($tiempoMaximo - $tiempoMinimo));
    }
    
    $tiempoEstimado = max($tiempoMinimo, min($tiempoMaximo, round($tiempoEstimado)));
@endphp
<div id="documentos-estadisticas-component-{{ $ayudaSolicitadaId }}" data-ayuda-id="{{ $ayudaSolicitadaId }}">
<h3 class="text-lg font-bold mb-2 mt-4">Información de documentos</h3>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-2">
    {{-- Completados --}}
    <div class="p-3 rounded-lg border bg-accent/50 border-primary/20 transition-all duration-200 hover:scale-[1.02]">
        <div class="flex items-center gap-2 mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-check w-4 h-4 text-primary">
                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                <path d="m9 15 2 2 4-4"></path>
            </svg>
            <span class="text-xs text-muted-foreground">Completados</span>
        </div>
        <p class="font-semibold text-foreground">{{ $completados }}<span class="text-muted-foreground font-normal">/{{ $totalDocumentos }}</span></p>
    </div>
    
    {{-- Pendientes --}}
    <div class="p-3 rounded-lg border bg-warning-bg border-warning/20 transition-all duration-200 hover:scale-[1.02]">
        <div class="flex items-center gap-2 mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-4 h-4 text-warning">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                <path d="M12 9v4"></path>
                <path d="M12 17h.01"></path>
            </svg>
            <span class="text-xs text-muted-foreground">Pendientes</span>
        </div>
        <p class="font-semibold text-foreground">{{ $pendientes }}</p>
    </div>
    
    {{-- Rechazados --}}
    <div class="p-3 rounded-lg border bg-destructive/5 border-destructive/20 transition-all duration-200 hover:scale-[1.02]">
        <div class="flex items-center gap-2 mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert w-4 h-4 text-destructive">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                <path d="M12 9v4"></path>
                <path d="M12 17h.01"></path>
            </svg>
            <span class="text-xs text-muted-foreground">Rechazados</span>
        </div>
        <p class="font-semibold text-foreground">{{ $rechazados }}</p>
    </div>
    
    {{-- Tiempo estimado --}}
    <div class="p-3 rounded-lg border bg-secondary/50 border-border transition-all duration-200 hover:scale-[1.02]">
        <div class="flex items-center gap-2 mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-4 h-4 text-muted-foreground">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="text-xs text-muted-foreground">Tiempo est.</span>
        </div>
        <p class="font-semibold text-foreground">~{{ round($tiempoEstimado) }} min</p>
    </div>
</div>

<style>
    /* Estilos para estadísticas de documentos */
    .grid {
        display: grid;
    }

    .grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media (min-width: 640px) {
        .sm\:grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .gap-3 {
        gap: 0.75rem;
    }

    .bg-accent\/50 {
        background-color: rgba(84, 222, 189, 0.1);
    }

    .border-primary\/20 {
        border-color: rgba(84, 222, 189, 0.2);
    }

    .bg-warning-bg {
        background-color: rgba(254, 243, 199, 0.3);
    }

    .border-warning\/20 {
        border-color: rgba(250, 204, 21, 0.2);
    }

    .bg-destructive\/5 {
        background-color: rgba(239, 68, 68, 0.05);
    }

    .border-destructive\/20 {
        border-color: rgba(239, 68, 68, 0.2);
    }

    .bg-secondary\/50 {
        background-color: rgba(243, 244, 246, 0.5);
    }

    .border-border {
        border-color: #e5e7eb;
    }

    .text-primary {
        color: #54debd;
    }

    .text-warning {
        color: #facc15;
    }

    .text-destructive {
        color: #ef4444;
    }

    .text-muted-foreground {
        color: #6b7280;
    }

    .text-foreground {
        color: #1f2937;
    }

    .hover\:scale-\[1\.02\]:hover {
        transform: scale(1.02);
    }

    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 0.15s;
    }

    .duration-200 {
        transition-duration: 0.2s;
    }

    .w-4 {
        width: 1rem;
    }

    .h-4 {
        height: 1rem;
    }

    .text-xs {
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .font-semibold {
        font-weight: 600;
    }

    .font-normal {
        font-weight: 400;
    }

    .items-center {
        align-items: center;
    }

    .mb-1 {
        margin-bottom: 0.25rem;
    }

    .p-3 {
        padding: 0.75rem;
    }

    .rounded-lg {
        border-radius: 0.5rem;
    }

    .border {
        border-width: 1px;
    }

    .mt-4 {
        margin-top: 1rem;
    }
</style>
</div>

