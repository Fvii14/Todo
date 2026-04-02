@props(['ayudaSolicitada'])

<ul class="list-unstyled mt-2">
    @foreach ($ayudaSolicitada->subsanacionDocumentos as $doc)
        @if (!Str::contains($doc->nombre ?? '', 'Recibo') && !Str::contains($doc->slug ?? '', 'recibo'))
            @php
                // Estado visual para fondo
                $estado = $doc->estado ?? 'faltante';
                $background = match ($estado) {
                    'subido' => 'bg-warning-subtle', // En revisión
                    'rechazado' => 'bg-danger-subtle', // Necesita reintento
                    default => 'bg-white',
                };

                // Nombre y descripción (con fallback)
                $nombreDoc = $doc->document->name ?? ($doc->nombre ?? ucfirst(str_replace('_', ' ', $doc->slug)));
                $descripcionDoc = $doc->document->description ?? ($doc->description ?? '');
            @endphp

            <li class="border rounded p-3 mb-2 shadow-sm {{ $background }}">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                    {{-- Nombre del documento y descripción --}}
                    <div class="text-start">
                        <span class="fw-semibold text-dark">{{ $nombreDoc }}</span>
                    </div>

                    {{-- Botones según estado --}}
                    <div class="d-flex flex-column align-items-center align-items-md-end text-center text-md-end w-100 w-md-auto">

                        {{-- Documento subido pero en revisión --}}
                        @if ($estado === 'subido')
                            <div class="text-muted">⏳ En revisión por nuestro equipo</div>

                        {{-- Documento rechazado --}}
                        @elseif ($estado === 'rechazado')
                            <div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>
                            <button class="btn btn-sm btn-warning mt-2 btn-secondary"
                                onclick="openModal(
                                    {{ $doc->document_id }},
                                    '{{ addslashes($nombreDoc) }}',
                                    '{{ $doc->slug }}',
                                    {{ $doc->multi_upload ? 'true' : 'false' }},
                                    `{{ addslashes($descripcionDoc) }}`,
                                    {{ $ayudaSolicitada->id }},
                                    true,
                                    {{ $doc->id }}
                                )">
                                Reintentar subida
                            </button>

                        {{-- Documento faltante --}}
                        @else
                            <button class="btn btn-sm btn-primary"
                                onclick="openModal(
                                    {{ $doc->document_id }},
                                    '{{ addslashes($nombreDoc) }}',
                                    '{{ $doc->slug }}',
                                    {{ $doc->multi_upload ? 'true' : 'false' }},
                                    `{{ addslashes($descripcionDoc) }}`,
                                    {{ $ayudaSolicitada->id }},
                                    true,
                                    {{ $doc->id }}
                                )">
                                Subir ahora
                            </button>
                        @endif
                    </div>
                </div>
            </li>
        @endif
    @endforeach
</ul>
