@props(['ayudaSolicitada'])

<div class="accordion mb-4" id="accordionRecibosSubsanacion-{{ $ayudaSolicitada->id }}">
    <div class="accordion-item">
        <p class="fw-bold text-dark mb-3">📆 Recibos solicitados en la subsanación:</p>

        <div class="accordion-body position-relative" style="background-color: #cfe2ff; padding: 20px;">
            
            {{-- Slider --}}
            <div id="reciboSliderSubsanacion-{{ $ayudaSolicitada->id }}"
                 class="d-flex gap-3 px-3 mx-auto"
                 style="max-width: 960px; overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none;">
                 
                @foreach ($ayudaSolicitada->subsanacionDocumentos as $doc)
                    @if (Str::contains($doc->nombre ?? '', 'Recibo') || Str::contains($doc->slug ?? '', 'recibo'))
                        @php
                            $estado = $doc->estado ?? 'faltante';
                            $background = match($estado) {
                                'pendiente' => 'bg-warning-subtle',
                                'rechazado' => 'bg-danger-subtle',
                                default => 'white'
                            };
                        @endphp
                        
                        <div class="card card-recibo p-3 shadow-sm text-center {{ $background }}"
                             style="min-width: 180px; max-width: 180px; height: 230px; display: flex; flex-direction: column; justify-content: space-between;">
                             
                            <div class="fw-bold medium">{{ $doc->nombre ?? 'Recibo' }}</div>

                            {{--Botón subir o reintentar --}}
                            @if ($estado === 'rechazado')
                                <button class="btn btn-sm btn-warning"
                                        onclick="openModal({{ $doc->id }}, '{{ $doc->nombre }}', '{{ $doc->slug }}', false, '', {{ $ayudaSolicitada->id }})">
                                    Reintentar subida
                                </button>
                            @elseif ($estado === 'pendiente')
                                <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
                            @else
                                <button class="btn btn-sm btn-primary"
                                        onclick="openModal({{ $doc->id }}, '{{ $doc->nombre }}', '{{ $doc->slug }}', false, '', {{ $ayudaSolicitada->id }})">
                                    Subir ahora
                                </button>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
