<div class="tramitacion-en-seguimiento-container"
    style="
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
    <div
        style="
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid #3b82f6;
    ">
        <div
            style="
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        ">
            <span style="font-size: 2rem;">⏳</span>
        </div>
        <div>
            <h3
                style="
                color: #2d3748;
                font-size: 1.75rem;
                font-weight: 700;
                margin: 0;
            ">
                Pendiente de Apertura</h3>
            <p
                style="
                color: #3b82f6;
                font-size: 1.1rem;
                margin: 0.25rem 0 0 0;
                font-weight: 600;
            ">
                La convocatoria está <strong>PENDIENTE</strong> de abrirse</p>
        </div>
    </div>

    <!-- Mensaje principal -->
    <div
        style="
        background: #f0f9ff;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #3b82f6;
    ">
        <p
            style="
            color: #2d3748;
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0 0 1rem 0;
            text-align: center;
        ">
            ⏳ <strong>La convocatoria de esta ayuda aún no se ha abierto.</strong>
        </p>
        <p
            style="
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
            text-align: center;
        ">
            Estamos esperando a que la Administración publique la convocatoria oficial. Te
            avisaremos en cuanto esté disponible.
        </p>
    </div>

    <!-- Tabs para documentos y datos -->
    <ul class="nav nav-tabs mt-4" id="tab-{{ $ayudaSolicitada->id }}" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="docs-tab-{{ $ayudaSolicitada->id }}"
                data-bs-toggle="tab" data-bs-target="#docs-{{ $ayudaSolicitada->id }}"
                type="button" role="tab" aria-controls="docs-{{ $ayudaSolicitada->id }}"
                aria-selected="true">📄 Documentos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="datos-tab-{{ $ayudaSolicitada->id }}" data-bs-toggle="tab"
                data-bs-target="#datos-{{ $ayudaSolicitada->id }}" type="button" role="tab"
                aria-controls="datos-{{ $ayudaSolicitada->id }}" aria-selected="false">📝
                Datos</button>
        </li>
    </ul>
    <div class="tab-content mt-3" id="tabContent-{{ $ayudaSolicitada->id }}">
        <div class="tab-pane fade show active" id="docs-{{ $ayudaSolicitada->id }}" role="tabpanel"
            aria-labelledby="docs-tab-{{ $ayudaSolicitada->id }}">
            <x-ayuda-card.documentos :ayudaSolicitada="$ayudaSolicitada" />
        </div>
        <div class="tab-pane fade" id="datos-{{ $ayudaSolicitada->id }}" role="tabpanel"
            aria-labelledby="datos-tab-{{ $ayudaSolicitada->id }}">
            <div class="border rounded p-3 bg-white shadow-sm">
                <div
                    class="border rounded p-3 shadow-sm @if ($estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false) bg-green-100 text-green-800 @endif">
                    <h5 class="fw-bold mb-3">🚹 Datos de Solicitante</h5>
                    @if ($estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false)
                        <div class="alert text-center my-2 py-1">
                            👍Tu formulario como solicitante parece estar completado. Te avisaremos
                            si falta algo.
                        </div>
                    @endif

                    @if (!($estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false))
                        <div class="d-flex align-items-center justify-content-center my-2"
                            style="min-width: 100%; max-width: 750px; gap: 10px;">
                            {{-- Estado a la izquierda --}}
                            <div style="width: 150px;" class="text-end pe-2">
                                <span
                                    class="fw-bold {{ $estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false ? 'text-success' : 'text-warning' }}">
                                    {{ $estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false ? '✅ Completado' : '⚠️ Pendiente' }}
                                </span>
                            </div>
                            {{-- Botón a la derecha --}}
                            <div style="width: 280px;" class="px-1">
                                <button
                                    class="btn w-100 p-2 {{ $estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false ? 'bg-green-100 text-green-900 border border-green-300 hover:bg-green-200 hover:text-black' : 'bg-blue-50 border border-dark hover:bg-blue-100 hover:text-black' }}"
                                    type="button" data-bs-toggle="collapse"
                                    data-bs-target="#datosSolicitante-{{ $ayudaSolicitada->id }}"
                                    aria-expanded="false"
                                    aria-controls="datosSolicitante-{{ $ayudaSolicitada->id }}"
                                    id="toggleButtonDatosSolicitante-{{ $ayudaSolicitada->id }}">
                                    {{ $estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false ? '✅ Revisar solicitante' : '📝 Formulario datos del solicitante' }}
                                </button>
                            </div>
                        </div>
                    @endif
                    <div class="collapse" id="datosSolicitante-{{ $ayudaSolicitada->id }}">
                        <form id="formDatosSolicitante-{{ $ayudaSolicitada->id }}" method="POST"
                            action="{{ route('solicitud.store.ajax') }}">
                            @csrf
                            <input type="hidden" name="questionnaire_id"
                                value="{{ $ayudaSolicitada->solicitud_questionnaire_id }}">
                            @foreach ($ayudaSolicitada->questions_solicitud ?? [] as $question)
                                @php
                                    $visibilidadInicial = $question['initial_visibility'] ?? true;
                                    $estiloDisplay = $visibilidadInicial ? '' : 'display: none;';
                                @endphp
                                <div class="question-item mb-3" data-id="{{ $question['id'] }}"
                                    style="{{ $estiloDisplay }}">
                                    <x-form-question :question="$question" />
                                </div>
                            @endforeach
                            <div class="mt-3 text-center">
                                <button type="submit" class="btn bg-success" style="color:white">
                                    <span class="btn-text">Guardar datos</span>
                                    <span class="btn-spinner d-none">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @if ($nConvivientes > 0 && $sectorAyuda == 'vivienda')
                    <div class="border rounded p-3 bg-white shadow-sm mt-3">
                        <h5 class="fw-bold mb-3">👥 Datos de convivientes</h5>
                        @if ($estadoConvivientes[$ayudaSolicitada->solicitud_questionnaire_id]['completo'] ?? false)
                            <div class="alert text-center my-2 py-1">
                                👍Tu formulario de conviviente parece estar completado. Te
                                avisaremos si falta algo.
                            </div>
                        @endif
                        @if (!($estadoConvivientes[$ayudaSolicitada->conviviente_questionnaire_id]['completo'] ?? false))
                            <p class="text-gray-700 mb-2 text-center">
                                Comparte este enlace para que esa persona complete su parte del
                                formulario o hazlo tú mismo:
                            </p>
                        @else
                            <div class="alert alert-success text-center my-2 py-1">
                                ✅ Los formularios de los convivientes parecen estar completados
                                correctamente. ¡Buen trabajo!
                            </div>
                        @endif
                        <ul class="list-unstyled mt-2 d-flex flex-column align-items-center gap-2">
                            @for ($i = 1; $i <= $nConvivientes; $i++)
                                @php
                                    $conv = $convivientes->firstWhere('index', $i);
                                    $errores =
                                        $conv &&
                                        isset(
                                            $estadoConvivientes[
                                                $ayudaSolicitada->conviviente_questionnaire_id
                                            ]['faltantes_por_conviviente'][$conv->id],
                                        )
                                            ? $estadoConvivientes[
                                                $ayudaSolicitada->conviviente_questionnaire_id
                                            ]['faltantes_por_conviviente'][$conv->id]
                                            : [];
                                    $completo = $conv && empty($errores);
                                @endphp
                                <li class="d-flex align-items-center justify-content-center"
                                    style="min-width: 100%; max-width: 750px;">
                                    {{-- Estado --}}
                                    <div style="width: 150px;" class="text-end pe-2">
                                        <span
                                            class="fw-bold {{ $completo ? 'text-success' : 'text-warning' }}">
                                            {{ $completo ? '✅ Completado' : '⚠️ Pendiente' }}
                                        </span>
                                    </div>
                                    {{-- Botón principal --}}
                                    <div style="width: 280px;" class="px-1">
                                        <button
                                            class="btn w-100 p-2 {{ $completo ? 'bg-green-100 text-green-900 border border-green-300 hover:bg-green-200 hover:text-black' : 'bg-blue-50 border border-dark hover:bg-blue-100 hover:text-black' }}"
                                            id="ButtonDatosConviviente-{{ $ayudaSolicitada->id }}-{{ $i }}"
                                            onclick="openConvivienteForm({{ $ayudaSolicitada->ayuda->id }}, {{ $ayudaSolicitada->conviviente_questionnaire_id }}, {{ $i }})">
                                            {{ $completo ? '✅ Revisar conviviente ' . $i : '📝 Añadir datos del conviviente ' . $i }}
                                        </button>
                                    </div>
                                    {{-- Botón compartir --}}
                                    <div style="width: 140px;" class="ps-1">
                                        <button class="btn w-100 btn-outline-secondary"
                                            onclick="generarEnlaceConviviente({{ $i }}, {{ $ayudaSolicitada->conviviente_questionnaire_id }})">
                                            🔗 Compartir
                                        </button>
                                    </div>
                                </li>
                            @endfor
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Estilos adicionales para mejorar la responsividad -->
<style>
    @media (max-width: 768px) {
        .tramitacion-en-seguimiento-container {
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .tramitacion-en-seguimiento-container h3 {
            font-size: 1.5rem !important;
        }
    }
</style>
