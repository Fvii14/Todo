<div>
    @php
        $questionnaireId = $ayudaSolicitada->formConvivientesId ?? null;
        if (!$questionnaireId || $questionnaireId == 0) {
            $questionnaireId = \App\Models\Questionnaire::where('ayuda_id', $ayudaSolicitada->ayuda->id)
                ->where('tipo', 'conviviente')
                ->value('id');
        }
        // Asegurar que sea un número válido
        $questionnaireId = $questionnaireId ? (int)$questionnaireId : null;
        $isCompletoGeneral = $questionnaireId ? ($estadoConvivientes[$questionnaireId]['completo'] ?? false) : false;
    @endphp

    @if ($isCompletoGeneral)
        <div class="alert alert-success text-center my-2 py-2">
            ✅ Los formularios de los convivientes parecen estar completados correctamente. ¡Buen trabajo!
        </div>
    @endif

    @foreach ($convivientes as $index => $conv)
        <ul class="list-unstyled mt-2 d-flex flex-column gap-3"></ul>
        @php
            // Saltar menores
            if (!$conv->esMayorQue(18)) {
                continue;
            }
            $numero = $index + 1;
        @endphp

        <li class="ttf-conv-item p-3">
            <div class="row">
                <div class="col-12">
                    <h6 class="fw-bold mb-2 d-flex align-items-center gap-2">
                        @if ($conv->nombre() == '')
                            👥 Datos {{ 'Conviviente ' . $numero }}
                        @else
                            👥 Datos {{ $conv->nombre() }}
                        @endif
                    </h6>
                </div>
            </div>
            <div class="row g-2 align-items-center justify-content-center my-2">
                <div class="col-12 col-sm-4 col-md-3 text-sm-end">
                    <span
                        class="badge rounded-pill {{ ($conv->completo ?? false) ? 'text-bg-success' : 'text-bg-warning' }} ttf-badge">
                        {{ ($conv->completo ?? false) ? '✅ Completado' : '⚠️ Pendiente' }}
                    </span>
                </div>
                <div class="col-12 col-sm-8 col-md-5">
                    @if ($questionnaireId && $questionnaireId > 0)
                        <button
                            class="btn {{ ($conv->completo ?? false) ? 'btn-outline-success' : 'btn-primary' }}
                            w-100 ttf-btn btn-sm py-2 lh-sm d-inline-flex align-items-center justify-content-center gap-2 flex-wrap text-center"
                            id="ButtonDatosConviviente-{{ $ayudaSolicitada->id }}-{{ $numero }}"
                            onclick="if (typeof openConvivienteForm === 'function') { openConvivienteForm({{ $ayudaSolicitada->ayuda->id }}, {{ $questionnaireId }}, {{ $numero }}); } else { console.error('openConvivienteForm no está disponible'); alert('Error: La función para abrir el formulario no está disponible. Por favor, recarga la página.'); }">
                            <span class="d-inline-flex align-items-center gap-1">
                                {!! ($conv->completo ?? false) ? '✅' : '📝' !!}
                            </span>
                            <span class="d-none d-sm-inline">
                                {!! ($conv->completo ?? false) ? "Revisar conviviente&nbsp;$numero" : "Añadir datos del conviviente&nbsp;$numero" !!}
                            </span>
                            <span class="d-inline d-sm-none">
                                {!! ($conv->completo ?? false) ? "Revisar&nbsp;$numero" : "Añadir datos&nbsp;$numero" !!}
                            </span>
                        </button>
                    @else
                        <div class="alert alert-warning p-2">
                            <small>No se encontró el cuestionario de convivientes para esta ayuda.</small>
                        </div>
                    @endif


                </div>
            </div>
             <p class="text-muted text-center mb-1">
            Comparte este enlace para que esa persona complete su parte del formulario o hazlo tú mismo:
        </p>

            @if ($questionnaireId)
                <div class="row g-2 align-items-center justify-content-center my-2">
                    <div class="col-12 col-sm-8 col-md-5 offset-sm-4 offset-md-3">
                        <button class="btn btn-secondary w-100 ttf-btn"
                            onclick="generarEnlaceConviviente({{ $numero }}, {{ $questionnaireId }})">
                            🔗 Compartir
                        </button>
                    </div>
                </div>
            @endif

        </li>

        </ul>
    @endforeach

</div>
