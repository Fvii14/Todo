@props(['ayudaSolicitada'])
@php
    use App\Helpers\SimulationHelper;
    use App\Models\AyudaDocumentoConviviente;
    use App\Models\Conviviente;
    use App\Models\Answer;
    use Illuminate\Support\Facades\DB;

    $userId = SimulationHelper::getCurrentUserId();
    $ayudaId = $ayudaSolicitada->ayuda->id ?? $ayudaSolicitada->ayuda_id;

    // Obtener documentos de convivientes necesarios para esta ayuda
    $documentosConvivientesNecesarios = AyudaDocumentoConviviente::with('documento')
        ->where('ayuda_id', $ayudaId)
        ->get();

    // Obtener convivientes del usuario
    $convivientes = Conviviente::where('user_id', $userId)->orderBy('index')->get()->keyBy('id');

    // Usar el método del servicio para obtener slugs especiales
    // Ahora el método obtiene las respuestas directamente desde la BD usando el userId
    $documentosAyudaService = app(\App\Services\DocumentosAyudaService::class);
    $slugsEspeciales = $documentosAyudaService->obtenerSlugsDocumentosEspecialesCondicionales(
        $ayudaId,
        $userId,
    );

    // Obtener documentos especiales que cumplen las condiciones
    // Estos documentos deben mostrarse siempre si cumplen las condiciones,
    // incluso si también están en documentos_faltantes
    $documentosEspeciales = \App\Models\AyudaDocumento::with('documento')
        ->where('ayuda_id', $ayudaId)
        ->get()
        ->filter(function ($docRel) use ($slugsEspeciales) {
            return $docRel->documento &&
                $docRel->documento->tipo === 'especial' &&
                in_array($docRel->documento->slug, $slugsEspeciales);
        });

    // Obtener slugs ya mostrados en documentos_configurados para evitar duplicados
    // Los documentos especiales condicionales pueden estar en documentos_faltantes,
    // pero deben mostrarse siempre si cumplen las condiciones
    $slugsMostradosConfigurados = collect($ayudaSolicitada->documentos_configurados ?? [])
        ->pluck('slug')
        ->toArray();
@endphp

<ul class="list-unstyled mt-2">

    {{-- 🔍 Documentos configurados como visibles (SOLICITANTE) --}}
    @if (!empty($ayudaSolicitada->documentos_configurados))
        @foreach ($ayudaSolicitada->documentos_configurados as $doc)
            @php
                $docSubido = null;
                if (
                    !empty($ayudaSolicitada->documentos_subidos) &&
                    isset($ayudaSolicitada->documentos_subidos[$doc['slug']])
                ) {
                    $docSubidoArray = $ayudaSolicitada->documentos_subidos[$doc['slug']];
                    $docSubido = is_array($docSubidoArray)
                        ? (object) $docSubidoArray
                        : $docSubidoArray;
                } else {
                    $docSubido = \App\Models\UserDocument::where(
                        'user_id',
                        SimulationHelper::getCurrentUserId(),
                    )
                        ->where('slug', $doc['slug'])
                        ->where('conviviente_index', null)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                }
                $backgroundClass = 'bg-white';
                $estado = 'faltante';

                if ($docSubido) {
                    $estadoDoc = is_array($docSubido)
                        ? $docSubido['estado'] ?? null
                        : $docSubido->estado ?? null;
                    if ($estadoDoc === 'pendiente') {
                        $backgroundClass = 'bg-warning-subtle';
                        $estado = 'pendiente';
                    } elseif ($estadoDoc === 'rechazado') {
                        $backgroundClass = 'bg-danger-subtle';
                        $estado = 'rechazado';
                    } elseif ($estadoDoc === 'validado') {
                        $backgroundClass = 'bg-success-subtle';
                        $estado = 'validado';
                    }
                }
            @endphp

            <li class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 border rounded p-3 mb-2 shadow-sm {{ $backgroundClass }}"
                style="border-left: 4px solid #54debd;" data-document-slug="{{ $doc['slug'] }}"
                data-document-type="solicitante">
                <div class="d-flex align-items-center gap-2">
                    @if ($estado === 'faltante')
                        <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                            alt="Falta documento" style="width: 20px; height: 20px;">
                    @elseif ($estado === 'pendiente')
                        <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                            alt="En revisión" style="width: 24px; height: 24px;">
                    @elseif ($estado === 'rechazado')
                        <img src="{{ asset('imagenes/32px-Cross_red_circle.svg.png') }}"
                            alt="Rechazado" style="width: 24px; height: 24px;">
                    @elseif ($estado === 'validado')
                        <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                            alt="Validado" style="width: 24px; height: 24px;">
                    @endif
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-dark">{{ $doc['name'] }}</span>
                        <small class="text-muted">👤 Solicitante</small>
                    </div>
                </div>

                <div
                    class="w-100 d-flex flex-column flex-md-row align-items-center justify-content-end gap-2 mt-2">
                    @if ($estado === 'faltante')
                        @if (!empty($doc['informative_clickable_text'] ?? null))
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="window.showInformativeDocSidebar(
                                        {{ json_encode($doc['informative_header_text'] ?? '') }},
                                        {{ json_encode($doc['informative_clickable_text'] ?? '') }},
                                        {{ json_encode($doc['informative_link'] ?? '') }},
                                        {{ json_encode($doc['informative_link_text'] ?? '') }},
                                        {{ $doc['id'] }},
                                        {{ json_encode($doc['name']) }},
                                        {{ json_encode($doc['slug']) }},
                                        {{ $doc['multi_upload'] ? 'true' : 'false' }}
                                    )">
                                <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
                            </button>
                        @endif
                        <button class="btn btn-sm btn-primary"
                            onclick="openModal({{ $doc['id'] }}, '{{ $doc['name'] }}', '{{ $doc['slug'] }}', {{ $doc['multi_upload'] ? 'true' : 'false' }}, '', null, false, null, null)">
                            Subir ahora
                        </button>
                    @elseif ($estado === 'pendiente')
                        <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
                    @elseif ($estado === 'rechazado')
                        <div class="d-flex flex-column align-items-end gap-2">
                            <div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>
                            @php
                                $notaRechazo = is_array($docSubido)
                                    ? $docSubido['nota_rechazo'] ?? null
                                    : $docSubido->nota_rechazo ?? null;
                            @endphp
                            @if (!empty($notaRechazo))
                                <div class="alert alert-danger mt-2 mb-0 py-2 px-3 w-100"
                                    role="alert">
                                    <strong>Nota del equipo:</strong> {{ $notaRechazo }}
                                </div>
                            @endif
                            <button class="btn btn-sm btn-warning mt-2 btn-secondary"
                                onclick="openModal({{ $doc['id'] }},'{{ $doc['name'] }}','{{ $doc['slug'] }}',{{ $doc['multi_upload'] ? 'true' : 'false' }},'',{{ $ayudaSolicitada->id }}, false, null, null)">
                                Reintentar subida
                            </button>
                        </div>
                    @elseif ($estado === 'validado')
                        <div class="text-success medium">✅ Documento validado</div>
                    @endif
                </div>
            </li>
        @endforeach
    @endif

    {{-- 🔍 Documentos faltantes del SOLICITANTE (que NO son recibos y NO están en documentos_configurados) --}}
    @php
        $documentosConfiguradosSlugs = collect($ayudaSolicitada->documentos_configurados ?? [])
            ->pluck('slug')
            ->toArray();
        $documentosFaltantesOriginales = collect(
            $ayudaSolicitada->documentos_faltantes ?? [],
        )->filter(fn($d) => !Str::contains($d['name'], 'Recibo'));
        $documentosFaltantesFiltrados = $documentosFaltantesOriginales->filter(
            fn($d) => !in_array($d['slug'], $documentosConfiguradosSlugs),
        );
    @endphp
    @foreach ($documentosFaltantesFiltrados as $doc)
        @php
            $docSubido = null;
            if (
                !empty($ayudaSolicitada->documentos_subidos) &&
                isset($ayudaSolicitada->documentos_subidos[$doc['slug']])
            ) {
                $docSubidoArray = $ayudaSolicitada->documentos_subidos[$doc['slug']];
                $docSubido = is_array($docSubidoArray) ? (object) $docSubidoArray : $docSubidoArray;
            } else {
                $docSubido = \App\Models\UserDocument::where(
                    'user_id',
                    SimulationHelper::getCurrentUserId(),
                )
                    ->where('slug', $doc['slug'])
                    ->where('conviviente_index', null)
                    ->orderBy('created_at', 'DESC')
                    ->first();
            }
            $backgroundClass = 'bg-white';
            $estado = 'faltante';

            if ($docSubido) {
                $estadoDoc = is_array($docSubido)
                    ? $docSubido['estado'] ?? null
                    : $docSubido->estado ?? null;
                if ($estadoDoc === 'pendiente') {
                    $backgroundClass = 'bg-warning-subtle';
                    $estado = 'pendiente';
                } elseif ($estadoDoc === 'rechazado') {
                    $backgroundClass = 'bg-danger-subtle';
                    $estado = 'rechazado';
                } elseif ($estadoDoc === 'validado') {
                    $backgroundClass = 'bg-success-subtle';
                    $estado = 'validado';
                }
            }
        @endphp

        <li class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 border rounded p-3 mb-2 shadow-sm {{ $backgroundClass }}"
            style="border-left: 4px solid #54debd;">
            <div class="d-flex align-items-center gap-2">
                @if ($estado === 'faltante')
                    <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                        alt="Falta documento" style="width: 20px; height: 20px;">
                @elseif ($estado === 'pendiente')
                    <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                        alt="En revisión" style="width: 24px; height: 24px;">
                @elseif ($estado === 'rechazado')
                    <img src="{{ asset('imagenes/32px-Cross_red_circle.svg.png') }}"
                        alt="Rechazado" style="width: 24px; height: 24px;">
                @elseif ($estado === 'validado')
                    <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                        alt="Validado" style="width: 24px; height: 24px;">
                @endif
                <div class="d-flex flex-column">
                    <span class="fw-semibold text-dark">{{ $doc['name'] }}</span>
                    <small class="text-muted">👤 Solicitante</small>
                </div>
            </div>

            <div class="w-100 d-flex justify-content-center justify-content-md-end mt-2">
                @if ($estado === 'faltante')
                    <button class="btn btn-sm btn-primary"
                        onclick="openModal({{ $doc['id'] }}, '{{ $doc['name'] }}', '{{ $doc['slug'] }}', {{ $doc['multi_upload'] ? 'true' : 'false' }}, `{{ addslashes($doc['description'] ?? '') }}`, null, false, null, null)">
                        Subir ahora
                    </button>
                @elseif ($estado === 'pendiente')
                    <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
                @elseif ($estado === 'rechazado')
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>
                        @if (!empty($docSubido?->nota_rechazo))
                            <div class="alert alert-danger mt-2 mb-0 py-2 px-3 w-100"
                                role="alert">
                                <strong>Nota del equipo:</strong> {{ $docSubido->nota_rechazo }}
                            </div>
                        @endif
                        <button class="btn btn-sm btn-warning mt-2 btn-secondary"
                            onclick="openModal({{ $doc['id'] }},'{{ $doc['name'] }}','{{ $doc['slug'] }}',{{ $doc['multi_upload'] ? 'true' : 'false' }},`{{ addslashes($doc['description'] ?? '') }}`,{{ $ayudaSolicitada->id ?? 'null' }}, false, null, null)">
                            Reintentar subida
                        </button>
                    </div>
                @elseif ($estado === 'validado')
                    <div class="text-success medium">✅ Documento validado</div>
                @endif
        <li
            class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 border rounded p-3 mb-2 shadow-sm {{ $backgroundClass }}">
            <div
                class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                    alt="Falta documento" style="width: 20px; height: 20px;">
                <div class="d-flex flex-column">
                    <span class="fw-semibold text-dark">{{ $doc['name'] }}</span>
                </div>
            </div>

            <div
                class="w-100 d-flex flex-column flex-md-row align-items-center justify-content-end gap-2 mt-2">
                @if (!empty($doc['informative_clickable_text'] ?? null))
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="window.showInformativeDocSidebar(
                                {{ json_encode($doc['informative_header_text'] ?? '') }},
                                {{ json_encode($doc['informative_clickable_text'] ?? '') }},
                                {{ json_encode($doc['informative_link'] ?? '') }},
                                {{ json_encode($doc['informative_link_text'] ?? '') }},
                                {{ $doc['id'] }},
                                {{ json_encode($doc['name']) }},
                                {{ json_encode($doc['slug']) }},
                                {{ $doc['multi_upload'] ? 'true' : 'false' }}
                            )">
                        <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
                    </button>
                @endif
                <button class="btn btn-sm btn-primary"
                    onclick="openModal({{ $doc['id'] }}, '{{ $doc['name'] }}', '{{ $doc['slug'] }}', {{ $doc['multi_upload'] ? 'true' : 'false' }}, `{{ addslashes($doc['description'] ?? '') }}`)">
                    Subir ahora
                </button>
            </div>
        </li>
    @endforeach

    {{-- 🔍 Documentos pendientes o rechazados (que NO están en faltantes y no son recibos) --}}
    @php
        $documentosPendientesRevision = \App\Models\UserDocument::where(
            'user_id',
            SimulationHelper::getCurrentUserId(),
        )
            ->whereIn('estado', ['pendiente', 'rechazado'])
            ->get()
            ->keyBy('slug');
    @endphp

    @foreach ($documentosPendientesRevision as $slug => $docSubido)
        @php
            $backgroundClass = 'bg-white';
            if ($docSubido->estado === 'pendiente') {
                $backgroundClass = 'bg-warning-subtle';
            } elseif ($docSubido->estado === 'rechazado') {
                $backgroundClass = 'bg-danger-subtle';
            }
        @endphp

        @if (
            !collect($ayudaSolicitada->documentos_faltantes)->pluck('slug')->contains($slug) &&
                !collect($ayudaSolicitada->documentos_configurados ?? [])->pluck('slug')->contains($slug) &&
                !Str::contains($slug, 'recibo'))
            <li class="border rounded p-3 mb-2 shadow-sm {{ $backgroundClass }}">
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                    {{-- Icono + nombre --}}
                    <div class="d-flex align-items-center gap-2 text-start w-100 w-md-auto">
                        @if ($docSubido->estado === 'pendiente')
                            <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                                alt="En revisión" style="width: 24px; height: 24px;">
                        @elseif($docSubido->estado === 'rechazado')
                            <img src="{{ asset('imagenes/32px-Cross_red_circle.svg.png') }}"
                                alt="Rechazado" style="width: 24px; height: 24px;">
                        @else
                            <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                                alt="Pendiente" style="width: 24px; height: 24px;">
                        @endif

                        <span
                            class="fw-semibold text-dark">{{ $docSubido->nombre_personalizado ?? $docSubido->slug }}</span>
                    </div>

                    {{-- Estado + botón --}}
                    <div
                        class="d-flex flex-column align-items-center align-items-md-end text-center text-md-end w-100 w-md-auto">
                        @if ($docSubido->estado === 'pendiente')
                            <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
                        @elseif($docSubido->estado === 'rechazado')
                            <div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>
                            @if (!empty($docSubido?->nota_rechazo))
                                <div class="alert alert-danger mt-2 mb-0 py-2 px-3 w-100"
                                    role="alert">
                                    <strong>Nota del equipo:</strong>
                                    {{ $docSubido->nota_rechazo }}
                                </div>
                            @endif
                            <button class="btn btn-sm btn-warning mt-2 btn-secondary"
                                onclick="openModal({{ $docSubido['document_id'] }},'{{ $docSubido['nombre_personalizado'] }}','{{ $slug }}',{{ $docSubido['multi_upload'] ? 'true' : 'false' }},`{{ addslashes($docSubido->description ?? '') }}`,{{ $docSubido['ayuda_solicitada_id'] ?? 'null' }})">
                                Reintentar subida
                            </button>
                        @endif
                    </div>
                </div>
            </li>
        @endif
    @endforeach

    {{-- 🔍 Documentos de convivientes necesarios (solo los que cumplen condiciones) --}}
    @php
        // Usar documentos de convivientes que cumplen condiciones si están disponibles
        // Si no, usar todos los documentos de convivientes (compatibilidad hacia atrás)
        $documentosConvivientesAMostrar =
            $ayudaSolicitada->documentos_convivientes_con_condiciones ??
            $documentosConvivientesNecesarios;
    @endphp

    @if ($documentosConvivientesAMostrar->isNotEmpty())
        @foreach ($documentosConvivientesAMostrar as $ayudaDocConviviente)
            @php
                $documento = $ayudaDocConviviente->documento;
                if (!$documento) {
                    continue;
                }

                // NO filtrar por slugs mostrados - mostrar TODOS los documentos de convivientes
                // incluso si el mismo documento es necesario para el solicitante

                // Obtener los IDs de los convivientes que deben ver este documento
                // Si no existe el atributo, mostrar para todos (compatibilidad hacia atrás)
                $convivientesIdsPermitidos = $ayudaDocConviviente->convivientes_ids ?? null;

                // Para cada conviviente, verificar si tiene el documento subido
                $documentosConvivientesPorConviviente = [];
                foreach ($convivientes as $conviviente) {
                    // Si hay convivientes_ids definidos, solo mostrar para esos convivientes
                    if (
                        $convivientesIdsPermitidos !== null &&
                        !in_array($conviviente->id, $convivientesIdsPermitidos)
                    ) {
                        continue; // Saltar este conviviente
                    }

                    $docSubido = \App\Models\UserDocument::where('user_id', $userId)
                        ->where('slug', $documento->slug)
                        ->where('conviviente_index', $conviviente->index)
                        ->orderBy('created_at', 'DESC')
                        ->first();

                    $estado = 'faltante';
                    $backgroundClass = 'bg-white';

                    if ($docSubido) {
                        if ($docSubido->estado === 'pendiente') {
                            $backgroundClass = 'bg-warning-subtle';
                            $estado = 'pendiente';
                        } elseif ($docSubido->estado === 'rechazado') {
                            $backgroundClass = 'bg-danger-subtle';
                            $estado = 'rechazado';
                        } elseif ($docSubido->estado === 'validado') {
                            $backgroundClass = 'bg-success-subtle';
                            $estado = 'validado';
                        }
                    }

                    $nombreConviviente = $conviviente->nombre();
                    if (empty($nombreConviviente)) {
                        $nombreConviviente = 'Conviviente ' . $conviviente->index;
                    }

                    $documentosConvivientesPorConviviente[] = [
                        'conviviente' => $conviviente,
                        'nombre' => $nombreConviviente,
                        'docSubido' => $docSubido,
                        'estado' => $estado,
                        'backgroundClass' => $backgroundClass,
                    ];
                }
            @endphp

            @if (!empty($documentosConvivientesPorConviviente))
                @foreach ($documentosConvivientesPorConviviente as $docConviviente)
                    <li class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 border rounded p-3 mb-2 shadow-sm {{ $docConviviente['backgroundClass'] }}"
                        style="border-left: 4px solid #dbb4ff;"
                        data-document-slug-conviviente="{{ $documento->slug }}"
                        data-conviviente-index="{{ $docConviviente['conviviente']->index }}"
                        data-document-type="conviviente">
                        <div class="d-flex align-items-center gap-2">
                            @if ($docConviviente['estado'] === 'faltante')
                                <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                                    alt="Falta documento" style="width: 20px; height: 20px;">
                            @elseif ($docConviviente['estado'] === 'pendiente')
                                <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                                    alt="En revisión" style="width: 24px; height: 24px;">
                            @elseif ($docConviviente['estado'] === 'rechazado')
                                <img src="{{ asset('imagenes/32px-Cross_red_circle.svg.png') }}"
                                    alt="Rechazado" style="width: 24px; height: 24px;">
                            @elseif ($docConviviente['estado'] === 'validado')
                                <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                                    alt="Validado" style="width: 24px; height: 24px;">
                            @endif
                            <div class="d-flex flex-column">
                                <span class="fw-semibold text-dark">{{ $documento->name }}</span>
                                <small class="text-muted">👤 Conviviente:
                                    {{ $docConviviente['nombre'] }}</small>
                            </div>
                        </div>

                        <div
                            class="w-100 d-flex justify-content-center justify-content-md-end mt-2">
                            @if ($docConviviente['estado'] === 'faltante')
                                <button class="btn btn-sm btn-primary"
                                    onclick="openModalConviviente({{ $documento->id }}, '{{ $documento->name }}', '{{ $documento->slug }}', {{ $documento->multi_upload ? 'true' : 'false' }}, '', {{ $docConviviente['conviviente']->id }}, {{ $docConviviente['conviviente']->index }})">
                                    Subir ahora
                                </button>
                            @elseif ($docConviviente['estado'] === 'pendiente')
                                <div class="text-muted medium">⏳ En revisión por nuestro equipo
                                </div>
                            @elseif ($docConviviente['estado'] === 'rechazado')
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <div class="text-danger medium">❌ Rechazado, vuelve a subirlo
                                    </div>
                                    @if (!empty($docConviviente['docSubido']?->nota_rechazo))
                                        <div class="alert alert-danger mt-2 mb-0 py-2 px-3 w-100"
                                            role="alert">
                                            <strong>Nota del equipo:</strong>
                                            {{ $docConviviente['docSubido']->nota_rechazo }}
                                        </div>
                                    @endif
                                    <button class="btn btn-sm btn-warning mt-2 btn-secondary"
                                        onclick="openModalConviviente({{ $documento->id }},'{{ $documento->name }}','{{ $documento->slug }}',{{ $documento->multi_upload ? 'true' : 'false' }},'',{{ $docConviviente['conviviente']->id }}, {{ $docConviviente['conviviente']->index }}, {{ $ayudaSolicitada->id ?? 'null' }})">
                                        Reintentar subida
                                    </button>
                                </div>
                            @elseif ($docConviviente['estado'] === 'validado')
                                <div class="text-success medium">✅ Documento validado</div>
                            @endif
                        </div>
                    </li>
                @endforeach
            @endif
        @endforeach
    @endif

    {{-- 🔍 Documentos especiales condicionales --}}
    @if ($documentosEspeciales->isNotEmpty())
        @foreach ($documentosEspeciales as $ayudaDocEspecial)
            @php
                $documento = $ayudaDocEspecial->documento;
                if (!$documento) {
                    continue;
                }

                // Verificar si ya está en documentos_configurados para evitar duplicados
                // NO filtrar por documentos_faltantes, ya que los especiales condicionales
                // deben mostrarse siempre si cumplen las condiciones
                if (in_array($documento->slug, $slugsMostradosConfigurados)) {
                    continue;
                }

                $docSubido = null;
                if (
                    !empty($ayudaSolicitada->documentos_subidos) &&
                    isset($ayudaSolicitada->documentos_subidos[$documento->slug])
                ) {
                    $docSubidoArray = $ayudaSolicitada->documentos_subidos[$documento->slug];
                    $docSubido = is_array($docSubidoArray)
                        ? (object) $docSubidoArray
                        : $docSubidoArray;
                } else {
                    $docSubido = \App\Models\UserDocument::where('user_id', $userId)
                        ->where('slug', $documento->slug)
                        ->where('conviviente_index', null)
                        ->orderBy('created_at', 'DESC')
                        ->first();
                }

                $backgroundClass = 'bg-white';
                $estado = 'faltante';

                if ($docSubido) {
                    $estadoDoc = is_array($docSubido)
                        ? $docSubido['estado'] ?? null
                        : $docSubido->estado ?? null;
                    if ($estadoDoc === 'pendiente') {
                        $backgroundClass = 'bg-warning-subtle';
                        $estado = 'pendiente';
                    } elseif ($estadoDoc === 'rechazado') {
                        $backgroundClass = 'bg-danger-subtle';
                        $estado = 'rechazado';
                    } elseif ($estadoDoc === 'validado') {
                        $backgroundClass = 'bg-success-subtle';
                        $estado = 'validado';
                    }
                }
            @endphp

            <li class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 border rounded p-3 mb-2 shadow-sm {{ $backgroundClass }}"
                style="border-left: 4px solid #9333ea;">
                <div class="d-flex align-items-center gap-2">
                    @if ($estado === 'faltante')
                        <img src="{{ asset('imagenes/—Pngtree—danger sign flat icon vector_9133214.png') }}"
                            alt="Falta documento" style="width: 20px; height: 20px;">
                    @elseif ($estado === 'pendiente')
                        <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                            alt="En revisión" style="width: 24px; height: 24px;">
                    @elseif ($estado === 'rechazado')
                        <img src="{{ asset('imagenes/32px-Cross_red_circle.svg.png') }}"
                            alt="Rechazado" style="width: 24px; height: 24px;">
                    @elseif ($estado === 'validado')
                        <img src="{{ asset('imagenes/—Pngtree—magnifying glass retriever png_4525348.png') }}"
                            alt="Validado" style="width: 24px; height: 24px;">
                    @endif
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-dark">{{ $documento->name }}</span>
                        <small class="text-muted"> 👤 Solicitante</small>
                    </div>
                </div>

                <div class="w-100 d-flex justify-content-center justify-content-md-end mt-2">
                    @if ($estado === 'faltante')
                        <button class="btn btn-sm btn-primary"
                            onclick="openModal({{ $documento->id }}, '{{ $documento->name }}', '{{ $documento->slug }}', {{ $documento->multi_upload ? 'true' : 'false' }}, '')">
                            Subir ahora
                        </button>
                    @elseif ($estado === 'pendiente')
                        <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
                    @elseif ($estado === 'rechazado')
                        <div class="d-flex flex-column align-items-end gap-2">
                            <div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>
                            @if (!empty($docSubido?->nota_rechazo))
                                <div class="alert alert-danger mt-2 mb-0 py-2 px-3 w-100"
                                    role="alert">
                                    <strong>Nota del equipo:</strong>
                                    {{ $docSubido->nota_rechazo }}
                                </div>
                            @endif
                            <button class="btn btn-sm btn-warning mt-2 btn-secondary"
                                onclick="openModal({{ $documento->id }},'{{ $documento->name }}','{{ $documento->slug }}',{{ $documento->multi_upload ? 'true' : 'false' }},'',{{ $ayudaSolicitada->id ?? 'null' }})">
                                Reintentar subida
                            </button>
                        </div>
                    @elseif ($estado === 'validado')
                        <div class="text-success medium">✅ Documento validado</div>
                    @endif
                </div>
            </li>
        @endforeach
    @endif
</ul>

@if (isset($ayudaSolicitada->condiciones_documentos))
    <script src="{{ asset('js/documentos-conditions.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener respuestas iniciales del usuario
            const respuestasIniciales = {};

            // Buscar respuestas en el DOM (si están disponibles)
            document.querySelectorAll('[data-question-id]').forEach(el => {
                const questionId = parseInt(el.getAttribute('data-question-id'), 10);
                if (questionId) {
                    let answer = null;
                    if (el.type === 'checkbox') {
                        if (el.name && el.name.includes('[]')) {
                            const checkboxes = document.querySelectorAll(
                                `[name="${el.name}"]:checked`);
                            answer = Array.from(checkboxes).map(cb => cb.value);
                        } else {
                            answer = el.checked ? '1' : '0';
                        }
                    } else if (el.type === 'radio') {
                        const radios = document.querySelectorAll(
                            `[name="${el.name}"]:checked`);
                        answer = radios.length > 0 ? radios[0].value : null;
                    } else if (el.multiple) {
                        answer = Array.from(el.selectedOptions).map(opt => opt.value);
                    } else {
                        answer = el.value;
                    }
                    if (answer !== null) {
                        respuestasIniciales[questionId] = answer;
                    }
                }
            });

            // Inicializar sistema de condiciones de documentos
            const condiciones = @json($ayudaSolicitada->condiciones_documentos ?? []);
            if (window.initDocumentosConditions) {
                window.initDocumentosConditions(condiciones, respuestasIniciales);
            }
        });
    </script>
@endif
