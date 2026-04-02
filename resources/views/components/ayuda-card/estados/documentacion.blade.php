<style>
    /* ====== Ajustes responsive suaves ====== */
    .ttf-tabs .nav-tabs {
        gap: .5rem;
        border: 0;
    }

    .ttf-tabs .nav-tabs .nav-link {
        border: 0;
        border-bottom: 2px solid transparent;
        padding: .5rem .75rem;
        font-size: .95rem;
        line-height: 1.2;
        min-width: auto;
        white-space: nowrap;
    }

    .ttf-tabs .nav-tabs.nav-fill>.nav-item {
        flex: 1 1 auto;
    }

    .ttf-tabs .nav-tabs .nav-link {
        border: 0;
        border-bottom: 2px solid transparent;
    }

    .ttf-tabs .nav-tabs .nav-link.active {
        border-bottom-color: var(--bs-primary);
        background: transparent;
    }

    .ttf-scroll-x {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Tarjetas y secciones */
    .ttf-card {
        border: 0;
        border-radius: .75rem;
        box-shadow: 0 6px 18px rgba(16, 24, 40, .08);
    }

    .ttf-card .card-header {
        background: linear-gradient(180deg, #f8fafc, #ffffff);
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    /* Listado convivientes: layout adaptable */
    .ttf-conv-item {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: .75rem;
    }

    .ttf-badge {
        padding: .5rem .75rem;
        font-weight: 600;
    }

    /* Botones tamaño cómodo en móvil */
    .ttf-btn {
        min-height: 44px;
    }

    @media (max-width: 576px) {

        /* Reducimos tamaño de los tabs en móvil */
        .ttf-tabs .nav-tabs {
            gap: .25rem;
        }

        .ttf-tabs .nav-tabs .nav-link {
            padding: .4rem .6rem;
            font-size: .9rem;
            line-height: 1.2;
            min-width: 120px;
            /* antes 200px: causaba corte */
        }

        .ttf-btn {
            min-height: 38px;
        }

        /* antes 44px */
    }

    /* Fix: enforce readable heading colors on light backgrounds */
    .ttf-card,
    .ttf-conv-item {
        color: var(--bs-body-color) !important;
    }

    .ttf-card h1,
    .ttf-card h2,
    .ttf-card h3,
    .ttf-card h4,
    .ttf-card h5,
    .ttf-card h6,
    .ttf-conv-item h1,
    .ttf-conv-item h2,
    .ttf-conv-item h3,
    .ttf-conv-item h4,
    .ttf-conv-item h5,
    .ttf-conv-item h6 {
        color: var(--bs-body-color) !important;
    }

    .ttf-card .card-header h5 {
        color: var(--bs-emphasis-color, var(--bs-body-color)) !important;
    }
</style>

<div class="border-top pt-4 mt-4">
    <style>
        .accordion-section {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 6px 18px rgba(16, 24, 40, 0.08);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .accordion-button-custom {
            width: 100%;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: transparent;
            border: none;
            transition: background-color 0.2s;
            text-align: left;
        }

        .accordion-button-custom:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .accordion-button-custom .icon-container {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.08);
            margin-right: 0.75rem;
        }

        .accordion-button-custom .content-left {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .accordion-button-custom .content-text {
            text-align: left;
        }

        .accordion-button-custom h2 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: var(--bs-body-color);
        }

        .accordion-button-custom p {
            font-size: 0.875rem;
            margin: 0;
            color: var(--bs-secondary-color);
        }

        .accordion-button-custom .chevron {
            width: 20px;
            height: 20px;
            color: var(--bs-secondary-color);
            transition: transform 0.3s;
        }

        .accordion-button-custom[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }

        .accordion-content {
            padding: 1rem 1.25rem;
        }
    </style>

    <!-- Desplegable: Datos del Solicitante -->
    <section class="accordion-section">
        <button type="button" class="accordion-button-custom" data-bs-toggle="collapse"
            data-bs-target="#datosSolicitante-{{ $ayudaSolicitada->id }}" aria-expanded="false"
            aria-controls="datosSolicitante-{{ $ayudaSolicitada->id }}"
            id="toggleButtonDatosSolicitante-{{ $ayudaSolicitada->id }}">
            <div class="content-left">
                <span class="icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </span>
                <div class="content-text">
                    <h2>Datos del solicitante</h2>
                    <p>Información personal, contacto y situación laboral</p>
                    @if ($estadoPrincipal[$ayudaSolicitada->id]['completo'] ?? false)
                        <div class="alert alert-success text-center my-2 py-2">
                            👍 Tu formulario como solicitante parece estar completado. Te avisaremos
                            si falta algo.
                        </div>
                    @else
                        <div class="alert alert-warning text-center my-2 py-2">
                            Tu formulario como solicitante parece estar incompleto. Por favor,
                            completa todos los
                            campos.
                        </div>
                    @endif
                </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="chevron">
                <path d="m6 9 6 6 6-6"></path>
            </svg>
        </button>
        <div class="collapse" id="datosSolicitante-{{ $ayudaSolicitada->id }}">
            <div class="accordion-content">

                <form id="formDatosSolicitante-{{ $ayudaSolicitada->id }}" method="POST"
                    action="{{ route('solicitud.store.ajax') }}">
                    @csrf
                    <input type="hidden" name="questionnaire_id"
                        value="{{ $ayudaSolicitada->solicitud_questionnaire_id }}">

                    <div class="row g-3">
                        @foreach ($ayudaSolicitada->questions_solicitud ?? [] as $question)
                            @php
                                $visibilidadInicial = $question['initial_visibility'] ?? true;
                                $estiloDisplay = $visibilidadInicial ? '' : 'display: none;';
                            @endphp
                            <div class="col-12 col-md-6 question-item"
                                data-id="{{ $question['id'] }}" style="{{ $estiloDisplay }}">
                                <x-form-question :question="$question" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 text-center">
                        <button type="submit" class="btn btn-success ttf-btn">
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
    </section>

    <!-- Desplegable: Convivientes -->
    @if ($ayudaTieneFormConvivientes)
        <section class="accordion-section">
            <button type="button" class="accordion-button-custom" data-bs-toggle="collapse"
                data-bs-target="#convivientes-{{ $ayudaSolicitada->id }}" aria-expanded="false"
                aria-controls="convivientes-{{ $ayudaSolicitada->id }}">
                <div class="content-left">
                    <span class="icon-container">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </span>
                    <div class="content-text">
                        <h2>Convivientes</h2>
                        <p>Personas que viven contigo (opcional)</p>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="chevron">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </button>
            <!-- Apartado convivientes -->

            <div class="collapse" id="convivientes-{{ $ayudaSolicitada->id }}">
                <div class="accordion-content"
                    id="contenedor-convivientes-{{ $ayudaSolicitada->id }}">
                    @php
                        $user = Auth::user();
                        $convivientes = App\Models\Conviviente::where('user_id', $user->id)
                            ->orderBy('index')
                            ->get();
                    @endphp

                    @if ($nConvivientes > 0)
                        @if ($estadoConvivientes[$ayudaSolicitada->solicitud_questionnaire_id]['completo'] ?? false)
                            <div class="alert alert-success mb-3">
                                ✅ Formularios completados
                            </div>
                        @endif
                        <x-ayuda-card.ayuda-convivientes-list :nConvivientes="$nConvivientes" :sectorAyuda="$sectorAyuda"
                            :estadoConvivientes="$estadoConvivientes" :convivientes="$ayudaSolicitada->convivientes ?? []" :ayudaSolicitada="$ayudaSolicitada" />

                        <div class="mt-3">
                            <button type="button" class="btn btn-sm text-white w-100 ttf-btn"
                                style="background-color: #dd8f37; border-color: #dd8f37; border-radius: 9px; padding-inline: 2rem;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCrearConviviente-{{ $ayudaSolicitada->id }}">
                                <i class="fas fa-user-plus me-2"></i> Añadir nuevo conviviente
                            </button>
                        </div>
                    @else
                        @if ($preFormConviviente)
                            <x-ayuda-card.ayuda-convivientes-preform :ayudaSolicitada="$ayudaSolicitada"
                                :preguntasPreForm="$preguntasPreForm" />
                        @else
                            <x-ayuda-card.ayuda-convivientes-empty />
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm text-white w-100 ttf-btn"
                                    style="background-color: #dd8f37; border-color: #dd8f37; border-radius: 9px; padding-inline: 2rem;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCrearConviviente-{{ $ayudaSolicitada->id }}">
                                    <i class="fas fa-user-plus me-2"></i> Añadir nuevo conviviente
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            {{-- Modal para crear conviviente --}}
            <x-ayuda-card.modal-crear-conviviente :ayudaSolicitada="$ayudaSolicitada" />

        </section>
    @endif
    <!-- Desplegable: Documentos -->
    <section class="accordion-section">
        <button type="button" class="accordion-button-custom" data-bs-toggle="collapse"
            data-bs-target="#docs-{{ $ayudaSolicitada->id }}" aria-expanded="true"
            aria-controls="docs-{{ $ayudaSolicitada->id }}">
            <div class="content-left">
                <span class="icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z">
                        </path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13">
                        </line>
                        <line x1="16" y1="17" x2="8" y2="17">
                        </line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </span>
                <div class="content-text">
                    <h2>Documentos</h2>
                    <p>Sube los documentos necesarios para tu solicitud</p>
                </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="chevron">
                <path d="m6 9 6 6 6-6"></path>
            </svg>
        </button>
        <div class="collapse show" id="docs-{{ $ayudaSolicitada->id }}">
            <div class="accordion-content">
                <x-ayuda-card.documentos :ayudaSolicitada="$ayudaSolicitada" />
            </div>
        </div>
    </section>

</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[id^="formPreFormConviviente-"]').forEach(form => {
            const ayudaId = form.id.split('-').pop();

            const respuestaDiv = document.getElementById(
                `respuestaConviviente-${ayudaId}`);

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector(
                    'button[type="submit"]');

                // Si ya está deshabilitado, NO enviar de nuevo
                if (submitBtn.disabled) return;

                // Deshabilitamos para evitar envíos múltiples
                submitBtn.disabled = true;
                submitBtn.textContent = "Guardando...";

                const formData = new FormData(form);
                respuestaDiv.innerHTML =
                    '<div class="alert alert-info">💾 Guardando respuestas...</div>';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector(
                                    'input[name="_token"]')
                                .value
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        respuestaDiv.innerHTML =
                            `<div class="alert alert-success">${result.message}</div>`;

                        const contenedor = document.getElementById(
                            `contenedor-convivientes-${ayudaId}`);
                        fetch(`/convivientes-refresh/${ayudaId}`)
                            .then(res => res.text())
                            .then(html => {
                                contenedor.innerHTML = html;
                            });

                    } else {
                        respuestaDiv.innerHTML =
                            `<div class="alert alert-danger">❌ ${result.message}</div>`;
                        submitBtn.disabled = false;
                        submitBtn.textContent = "Guardar";
                    }

                } catch (error) {
                    console.error(error);
                    respuestaDiv.innerHTML =
                        `<div class="alert alert-danger">⚠️ Error de conexión. Intenta de nuevo.</div>`;

                    // Rehabilitar botón porque ha fallado
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Guardar";
                }
            });
        });
    });

    // Manejo de "Ninguna de las anteriores" para preguntas múltiples
    function initMultipleNoneOption() {
        // Función para manejar la exclusión mutua
        function handleNoneOption(questionId, isChecked) {
            const regularCheckboxes = document.querySelectorAll(`.multiple-checkbox-${questionId}`);

            if (isChecked) {
                // Si se marca "Ninguna de las anteriores", desmarcar y deshabilitar todas las demás
                regularCheckboxes.forEach(cb => {
                    cb.checked = false;
                    cb.disabled = true;
                });
            } else {
                // Si se desmarca "Ninguna de las anteriores", habilitar todas las demás
                regularCheckboxes.forEach(cb => {
                    cb.disabled = false;
                });
            }
        }

        function handleRegularOption(questionId) {
            const noneCheckbox = document.querySelector(`.none-option-${questionId}`);
            if (noneCheckbox && noneCheckbox.checked) {
                // Si se marca una opción regular, desmarcar "Ninguna de las anteriores"
                noneCheckbox.checked = false;
                handleNoneOption(questionId, false);
            }
        }

        // Añadir event listeners a todos los checkboxes "Ninguna de las anteriores"
        document.querySelectorAll('[class*="none-option-"]').forEach(checkbox => {
            const questionId = checkbox.getAttribute('data-question-id');
            // Remover listeners anteriores para evitar duplicados
            const newCheckbox = checkbox.cloneNode(true);
            checkbox.parentNode.replaceChild(newCheckbox, checkbox);
            newCheckbox.addEventListener('change', function() {
                handleNoneOption(questionId, this.checked);
            });
        });

        // Añadir event listeners a todos los checkboxes regulares
        document.querySelectorAll('[class*="multiple-checkbox-"]').forEach(checkbox => {
            const questionId = checkbox.getAttribute('data-question-id');
            // Remover listeners anteriores para evitar duplicados
            if (!checkbox.hasAttribute('data-listener-added')) {
                checkbox.setAttribute('data-listener-added', 'true');
                checkbox.addEventListener('change', function() {
                    handleRegularOption(questionId);
                });
            }
        });
    }

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', initMultipleNoneOption);

    // Reinicializar cuando se muestre el collapse (por si se carga dinámicamente)
    document.addEventListener('shown.bs.collapse', function(e) {
        if (e.target.id && e.target.id.includes('datosSolicitante')) {
            setTimeout(initMultipleNoneOption, 100);
        }
    });

    // Actualizar el chevron cuando se abra/cierre un desplegable
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.accordion-button-custom').forEach(button => {
            button.addEventListener('click', function() {
                // El aria-expanded se actualiza automáticamente por Bootstrap
                // El CSS ya maneja la rotación del chevron basado en aria-expanded
            });
        });
    });
</script>
