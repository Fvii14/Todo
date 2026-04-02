<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ayuda->nombre_ayuda }} - Cuestionario de Ayuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #59edca;
            --primary-dark: #40d4b0;
        }

        .list-group-item {
            background-color: white;
            color: black;
            border: 1px solid #eee;
            transition: 0.3s ease;
        }

        .list-group-item:hover,
        .list-group-item:focus {
            background-color: var(--primary-color);
            color: white;
        }

        .list-group-item.active {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }


        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .progress-bar {
            background-color: var(--primary-color);
            height: 8px;
        }

        .info-box {
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .question-item {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .question-text {
            font-weight: 500;
            color: #333;
        }

        .answer-text {
            color: #666;
            background: #f9f9f9;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 5px;
            display: inline-block;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            border-radius: 50%;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
        }

        input:checked+.slider {
            background-color: #4CAF50;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .toggle-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
        }

        .label-left {
            color: #f44336;
        }

        .label-right {
            color: #4CAF50;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    {{-- Header --}}
    @include('components.header')
    <x-gtm-noscript />

    <div class="container-fluid py-4">
        <div class="row">
            {{-- Sidebar izquierda --}}
            <aside class="col-12 col-md-3 sidebar">
                <div class="list-group mb-4">
                    <a href="#" class="list-group-item list-group-item-action active">Ayudas disponibles</a>
                    <a href="#" class="list-group-item list-group-item-action">Ayudas solicitadas</a>
                </div>
            </aside>

            {{-- Contenido principal --}}
            <main class="col-12 col-md-9">
                <h1 class="mb-3">{{ $ayuda->nombre_ayuda }}</h1>

                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100"
                        aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <p class="text-muted mb-4">Puedes modificar cualquier dato haciendo click en el mismo</p>

                <form id="questionnaire-form"
                    action="{{ route('ayuda.solicitar', $ayuda->id) }}"
                    method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    @if (isset($ayuda->pago) && $ayuda->pago == 1 && $product_id_for_form)
                        <input type="hidden" name="product_id" value="{{ $product_id_for_form }}">
                    @endif

                    <div class="info-box">
                        <div class="row">
                            @if ($mostrarPreguntas)
                                {{-- Preguntas --}}
                                <div class="col-12 col-lg-6">
                                    <h4>Preguntas</h4>
                                    @foreach ($questions as $question)
                                        <div class="question-item">
                                            <div class="question-text">{{ $loop->iteration }}. {{ $question['text'] }}
                                            </div>
                                            <div class="answer-text">
                                                @if ($question['type'] === 'boolean')
                                                    <input type="hidden" name="answer[{{ $question['id'] }}]"
                                                        value="0">
                                                    <label class="switch">
                                                        <input type="checkbox" name="answer[{{ $question['id'] }}]"
                                                            value="1"
                                                            @if (old('answer.' . $question['id'], $question['answer']) == 1) checked @endif>
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <div class="toggle-labels">
                                                        <span class="label-left">No</span>
                                                        <span class="label-right">Sí</span>
                                                    </div>
                                                @else
                                                    <input type="{{ $question['type'] }}"
                                                        name="answer[{{ $question['id'] }}]"
                                                        value="{{ old('answer.' . $question['id'], $question['answer']) }}"
                                                        class="form-control">
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="col-12 col-lg-6">
                                <h4>Documentos requeridos</h4>
                                @foreach ($documentos as $doc)
                                    <div class="mb-4">
                                        <label for="documento_{{ $doc->id }}" class="form-label">
                                            {{ $doc->name }}
                                            @if ($doc->es_obligatorio)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        </br>


                                        @php
                                            $userDocument = \App\Models\UserDocument::where('user_id', auth()->id())
                                                ->where('document_id', $doc->id)
                                                ->first();

                                            $urlTemporal = null;

                                            if ($userDocument && app()->bound('App\Services\GcsUploaderService')) {
                                                try {
                                                    $gcs = app()->make(\App\Services\GcsUploaderService::class);
                                                    $urlTemporal = $gcs->getTemporaryUrl($userDocument->file_path);
                                                } catch (Exception $e) {
                                                    $urlTemporal = null;
                                                }
                                            }
                                        @endphp

                                        @if ($userDocument && ($url = gcs_url_or_null($userDocument->file_path)))
                                            <p class="mt-2">
                                                ✅ Documento ya aportado:
                                                <a href="{{ $url }}" target="_blank" class="text-primary">
                                                    {{ $userDocument->file_name }}
                                                </a>
                                            </p>
                                        @else
                                        @endif



                                        @if (Str::contains(Str::lower($doc->name), 'firma'))
                                            <div class="mt-2 {{ !$userDocument ? 'firma-obligatoria' : '' }}">
                                                <p class="text-muted">Firma con el ratón o el dedo si estás en móvil.
                                                </p>
                                                <canvas id="signature-pad" width="400" height="200"
                                                    style="border:1px solid #06f3c0; border-radius: 5px;"></canvas>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                        onclick="clearSignature()">Borrar firma</button>
                                                </div>
                                                <input type="hidden" name="firma_base64" id="firma_base64" required>

                                                <div id="firma-error" class="text-danger mt-2 d-none">
                                                    Por favor, realiza tu firma antes de enviar el formulario.
                                                </div>
                                            </div>
                                        @else
                                                <input type="file" class="form-control"
                                                    name="documento_{{ $doc->id }}"
                                                    id="documento_{{ $doc->id }}" accept="{{ $doc->allowed_types }}"
                                                > {{-- <--- Añade el cierre aquí --}}
                                                {{-- La línea comentada del required puede quedar como está o eliminarla si no la necesitas --}}
                                                {{-- @if ($doc->es_obligatorio && !$userDocument) required @endif --}}
                                        @endif
                                    </div>
                                @endforeach

                            </div>

                             @if (isset($ayuda->pago) && $ayuda->pago == 1)
                                </div>
                            @else
                                {{-- CASO: Ayuda SIN pago (pago != 1) - SÍ mostrar num_cuenta --}}
                                {{-- CAMBIO AQUÍ: de col-md-6 a col-12 --}}
                                <div class="col-12 mt-3"> 
                                    <div class="m-1">
                                        <label for="num_cuenta">Número de cuenta</label><span class="text-danger">*</span>
                                        <input type="text" maxlength="30" class="form-control mb-3" name="num_cuenta"
                                            id="num_cuenta" value="{{ old('num_cuenta', $num_cuenta ?? '') }}"
                                            placeholder="Introduce tu número de cuenta" required style="width: 300px;">
                                    </div>
                                </div>
                            @endif

                        </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Solicitar ayuda</button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <div class="modal fade" id="confirmAccountModal" tabindex="-1" aria-labelledby="confirmAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmAccountModalLabel">Confirmar Número de Cuenta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>El número de cuenta introducido es: <strong id="modalNumCuentaValue"></strong></p>
        <p>¿Es correcto y deseas continuar?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirmAccountNoBtn">No, deseo corregirlo</button>
        <button type="button" class="btn btn-primary" id="confirmAccountYesBtn">Sí, es correcto</button>
      </div>
    </div>
  </div>
</div>

    {{-- Footer --}}
    @include('components.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('questionnaire-form');
        const numCuentaInput = document.getElementById('num_cuenta');

        // Modal de confirmación
        const confirmModalElement = document.getElementById('confirmAccountModal');
        if (!confirmModalElement) {
            console.error('Modal element #confirmAccountModal not found.');
            return; 
        }
        const confirmModal = new bootstrap.Modal(confirmModalElement);
        const modalNumCuentaValueElement = document.getElementById('modalNumCuentaValue');
        const confirmAccountYesBtn = document.getElementById('confirmAccountYesBtn');

        if (!modalNumCuentaValueElement || !confirmAccountYesBtn) {
            console.error('Modal content elements (modalNumCuentaValue or confirmAccountYesBtn) not found.');
            return; 
        }

        // --- Lógica del Canvas para la firma (mantenida de tu script original) ---
        const canvas = document.getElementById("signature-pad");
        let ctx = null;
        let drawing = false;
        const firmaError = document.getElementById('firma-error'); // Definido aquí para acceso global dentro de DOMContentLoaded
        const isFirmaObligatoriaElement = document.querySelector('.firma-obligatoria');
        const isFirmaObligatoria = isFirmaObligatoriaElement !== null;
        const firmaBase64Input = document.getElementById("firma_base64");


        if (canvas) {
            ctx = canvas.getContext("2d");

            // Eventos para limpiar error de firma al empezar a dibujar
            canvas.addEventListener("mousedown", () => {
                if(firmaError) firmaError.classList.add('d-none');
                canvas.classList.remove('border', 'border-danger');
            });
            canvas.addEventListener("touchstart", () => {
                if(firmaError) firmaError.classList.add('d-none');
                canvas.classList.remove('border', 'border-danger');
            });

            function getPosition(e) {
                const rect = canvas.getBoundingClientRect();
                const x = (e.clientX || e.touches?.[0]?.clientX) - rect.left;
                const y = (e.clientY || e.touches?.[0]?.clientY) - rect.top;
                return { x, y };
            }

            function startDrawing(e) {
                e.preventDefault();
                drawing = true;
                ctx.beginPath();
                const pos = getPosition(e);
                ctx.moveTo(pos.x, pos.y);
            }

            function draw(e) {
                e.preventDefault();
                if (!drawing) return;
                const pos = getPosition(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            }

            function stopDrawing(e) {
                e.preventDefault();
                drawing = false;
            }

            window.clearSignature = function() { // Adjuntado a window para ser accesible desde el HTML onclick
                if(ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
                if(firmaBase64Input) firmaBase64Input.value = "";
            }

            canvas.addEventListener("mousedown", startDrawing);
            canvas.addEventListener("mousemove", draw);
            canvas.addEventListener("mouseup", stopDrawing);
            canvas.addEventListener("mouseleave", stopDrawing);
            canvas.addEventListener("touchstart", startDrawing);
            canvas.addEventListener("touchmove", draw);
            canvas.addEventListener("touchend", stopDrawing);
        }
        // --- Fin de la lógica del Canvas ---

        // Función para verificar si el canvas está vacío (necesaria si se usa firma)
        function isCanvasEmpty(canvasToCheck) {
            if (!canvasToCheck) return true; // Si no hay canvas, se considera "vacío" para esta lógica
            const blank = document.createElement('canvas');
            blank.width = canvasToCheck.width;
            blank.height = canvasToCheck.height;
            return canvasToCheck.toDataURL() === blank.toDataURL();
        }
        
        // Función consolidada para validaciones y procesamiento
        function validateAndSubmitActions() {
            let allValid = true;

            // 1. Validación de preguntas (similar a tu lógica original)
            document.querySelectorAll('input[name^="answer["]').forEach(function(inputField) {
                const isCheckbox = inputField.type === 'checkbox';
                const isHidden = inputField.type === 'hidden';
                if (!isCheckbox && !isHidden && inputField.value.trim() === '' && inputField.hasAttribute('required')) { 
                    // Considera validar solo si el campo es visible y requerido
                    // Por ahora, si no es checkbox/hidden y está vacío, lo marcamos inválido
                    // Podrías añadir una clase 'required-question' a los inputs que realmente lo son
                    allValid = false;
                }
            });

            if (!allValid) {
                alert('Por favor, responde todas las preguntas obligatorias.');
                return false;
            }

            // 2. Validación y procesamiento de la firma (si existe y es obligatoria)
            if (canvas && firmaBase64Input) { // Solo si los elementos de firma existen
                if (isFirmaObligatoria && isCanvasEmpty(canvas)) {
                    if (firmaError) firmaError.classList.remove('d-none');
                    canvas.classList.add('border', 'border-danger');
                    alert('Por favor, realiza tu firma antes de enviar el formulario.');
                    allValid = false;
                } else {
                    if (firmaError) firmaError.classList.add('d-none');
                    canvas.classList.remove('border', 'border-danger');
                    const dataURL = canvas.toDataURL("image/png");
                    firmaBase64Input.value = dataURL;
                }
            } else if (isFirmaObligatoria) { 
                // Si la firma es obligatoria pero el canvas o el input hidden no existen.
                console.error("Error de configuración: Elementos de firma requeridos no encontrados.");
                alert("Error de configuración con el campo de firma. Contacta al administrador.");
                allValid = false;
            }
            
            return allValid;
        }

        form.addEventListener('submit', function(event) {
        event.preventDefault(); // ¡Importante! Detener el envío automático

        const numCuentaInputField = document.getElementById('num_cuenta'); // Intenta obtener el campo

        if (numCuentaInputField) {
            // Si el campo num_cuenta EXISTE, mostrar el popup de confirmación
            const numCuentaValue = numCuentaInputField.value;
            if (modalNumCuentaValueElement) {
                modalNumCuentaValueElement.textContent = numCuentaValue;
            }
            confirmModal.show(); // Mostrar el popup de Bootstrap
        } else {
            // Si el campo num_cuenta NO EXISTE, saltar el popup de num_cuenta
            // y proceder directamente con las otras validaciones y el envío.
            if (validateAndSubmitActions()) {
                form.submit(); // Envía el formulario programáticamente
            }
        }
    });

    // El manejador para el botón "Sí, es correcto" del Modal (confirmAccountYesBtn)
    // no necesita cambios, ya que solo se activa si el modal fue mostrado.
    if (confirmAccountYesBtn) {
        confirmAccountYesBtn.addEventListener('click', function() {
            confirmModal.hide(); 
            if (validateAndSubmitActions()) {
                form.submit(); 
            }
        });
    }
        
        // El botón "No, deseo corregirlo" del modal simplemente lo cierra (data-bs-dismiss="modal")
        // y no hace nada más, permitiendo al usuario editar.

        // IMPORTANTE: Asegúrate de eliminar o comentar tus antiguos event listeners separados
        // para el evento 'submit' del formulario 'questionnaire-form', ya que ahora toda la lógica
        // de validación y envío se gestiona a través del popup.
        // Por ejemplo:
        // document.getElementById('questionnaire-form').addEventListener('submit', function(e) { /* CÓDIGO ANTIGUO COMENTADO */ });
        // form.addEventListener("submit", function(e) { /* CÓDIGO ANTIGUO COMENTADO */ });

    });
</script>
</body>

</html>