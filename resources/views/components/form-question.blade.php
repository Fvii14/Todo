<div class="mb-3 row">
    <label class="form-label fw-semibold">{{ $question['text'] }}</label>

    {{-- Campo tipo texto --}}
    @if ($question['type'] === 'string')
        @php
            $answerValue = is_array($question['answer'] ?? null) ? '' : ($question['answer'] ?? '');
        @endphp
        <input type="text" name="answers[{{ $question['id'] }}]" value="{{ $answerValue }}"
            class="form-control col-12 col-sm-6"
            @if (isset($question['validation']['pattern']) && $question['validation']['pattern'] != '') pattern="{{ $question['validation']['pattern'] }}" @endif
            placeholder="{{ $question['subtext'] ?? '' }}">

        {{-- Campo tipo número --}}
    @elseif ($question['type'] === 'integer')
        @php
            $answerValue = is_array($question['answer'] ?? null) ? '' : ($question['answer'] ?? '');
        @endphp
        <input type="number" name="answers[{{ $question['id'] }}]" value="{{ $answerValue }}"
            class="form-control col-12 col-sm-6" placeholder="{{ $question['subtext'] ?? '' }}">

        {{-- Campo tipo boolean (Sí / No) --}}
    @elseif ($question['type'] === 'boolean')
        @php
            $answerValue = is_array($question['answer'] ?? null) ? '' : ($question['answer'] ?? '');
        @endphp
        <select name="answers[{{ $question['id'] }}]" class="form-select col-12 col-sm-6">
            <option value="0" {{ $answerValue == '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ $answerValue == '1' ? 'selected' : '' }}>Sí</option>
            
        </select>

        {{-- Campo tipo select (una opción) --}}
    @elseif ($question['type'] === 'select')
        @php
            $rawAnswer = $question['answer'] ?? null;
            // Si la respuesta es JSON, parsearla
            if (is_string($rawAnswer) && (strpos($rawAnswer, '[') === 0 || strpos($rawAnswer, '{') === 0)) {
                $decoded = json_decode($rawAnswer, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Si es un array, tomar el primer valor
                    $rawAnswer = !empty($decoded) ? reset($decoded) : '';
                }
            }
            $answerValue = is_array($rawAnswer) ? '' : ($rawAnswer ?? '');
        @endphp
        <select name="answers[{{ $question['id'] }}]" class="form-select">
            <option value="-1">Seleccione una opción</option>
            @foreach ($question['options'] as $key => $option)
                @php
                    $value = is_numeric($key) ? $option : $key;
                    $selected = old('answers.' . $question['id'], $answerValue);
                    // Comparar tanto por valor como por opción (por si la respuesta es el texto de la opción)
                    $isSelected = ($selected == $value || $selected == $option || $answerValue == $value || $answerValue == $option);
                @endphp
                <option value="{{ $value }}" @if ($isSelected) selected @endif>
                    {{ $option }}
                </option>
            @endforeach
        </select>

        {{-- Campo tipo multiple (checkboxes) --}}
    @elseif ($question['type'] === 'multiple')
        @php
            $rawAnswer = $question['answer'] ?? null;
            $selectedOptions = [];
            
            // Parsear la respuesta JSON si es string
            if (is_string($rawAnswer)) {
                $decoded = json_decode($rawAnswer, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $selectedOptions = $decoded;
                } elseif ($rawAnswer !== '') {
                    $selectedOptions = [$rawAnswer];
                }
            } elseif (is_array($rawAnswer)) {
                $selectedOptions = $rawAnswer;
            }
            
            // Convertir valores de opciones a claves (keys)
            // Si la respuesta contiene valores como "Persona con discapacidad", buscar la clave correspondiente
            $selectedKeys = [];
            foreach ($selectedOptions as $selectedValue) {
                // Buscar la clave que corresponde a este valor
                foreach ($question['options'] as $key => $optionValue) {
                    if ($selectedValue == $optionValue || $selectedValue == $key) {
                        $selectedKeys[] = $key;
                        break;
                    }
                }
            }
            
            $hasNoneSelected = in_array(-1, $selectedKeys) || in_array('-1', $selectedKeys) || 
                               in_array(-1, $selectedOptions) || in_array('-1', $selectedOptions) ||
                               $rawAnswer === -1 || $rawAnswer === '-1';
        @endphp
        @foreach ($question['options'] as $key => $value)
            <div class="form-check col-12 col-sm-6">
                <input type="checkbox" 
                    name="answers[{{ $question['id'] }}][]" 
                    value="{{ $key }}"
                    class="form-check-input multiple-checkbox-{{ $question['id'] }}"
                    data-question-id="{{ $question['id'] }}"
                    {{ in_array($key, $selectedKeys) ? 'checked' : '' }}
                    {{ $hasNoneSelected ? 'disabled' : '' }}>
                <label class="form-check-label">{{ $value }}</label>
            </div>
        @endforeach
        {{-- Opción "Ninguna de las anteriores" --}}
        <div class="form-check col-12 col-sm-6 mt-2" style="border-top: 1px solid #dee2e6; padding-top: 0.5rem;">
            <input type="checkbox" 
                name="answers[{{ $question['id'] }}][]" 
                value="-1"
                class="form-check-input none-option-{{ $question['id'] }}"
                data-question-id="{{ $question['id'] }}"
                {{ $hasNoneSelected ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold">Ninguna de las anteriores</label>
        </div>

        {{-- Campo tipo fecha --}}
    @elseif ($question['type'] === 'date')
        @php
            $answerValue = is_array($question['answer'] ?? null) ? '' : ($question['answer'] ?? '');
        @endphp
        <input type="date" name="answers[{{ $question['id'] }}]" value="{{ $answerValue }}"
            class="form-control col-12 col-sm-6" placeholder="{{ $question['subtext'] ?? '' }}">
    @endif

    {{-- Validación personalizada (si tiene mensaje de error) --}}
    {{-- @if ($question['validation']['error_message'])
        <div class="form-text text-danger mt-1 col-12">
            {{ $question['validation']['error_message'] }}
        </div>
    @endif --}}
</div>

@once
@push('scripts')
<script>
    // Interceptar submit de formularios que contienen form-question
    (function() {
        // Evitar ejecutar múltiples veces
        if (window.formQuestionAjaxInitialized) {
            return;
        }
        window.formQuestionAjaxInitialized = true;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Buscar todos los formularios y verificar si contienen el componente form-question
            const allForms = document.querySelectorAll('form');
            const forms = Array.from(allForms).filter(function(form) {
                return form.querySelector('.mb-3.row') !== null;
            });
            
            forms.forEach(function(form) {
                // Verificar si ya tiene un listener de AJAX (evitar duplicados)
                if (form.dataset.ajaxHandler === 'true') {
                    return;
                }
                
                // Marcar el formulario para evitar duplicados
                form.dataset.ajaxHandler = 'true';
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const answers = {};
                const processedFormData = new FormData();
                
                // Separar campos normales de las respuestas
                for (const [key, value] of formData.entries()) {
                    if (!key.startsWith('answers[')) {
                        processedFormData.append(key, value);
                    }
                }
                
                // Procesar respuestas
                for (const [key, value] of formData.entries()) {
                    if (key.startsWith('answers[')) {
                        const match = key.match(/answers\[(\d+)\](?:\[\])?/);
                        if (match) {
                            const questionId = match[1];
                            if (!answers[questionId]) {
                                answers[questionId] = [];
                            }
                            answers[questionId].push(value);
                        }
                    }
                }
                
                // Procesar respuestas múltiples (manejar -1 para "Ninguna de las anteriores")
                Object.keys(answers).forEach(questionId => {
                    const noneCheckbox = form.querySelector(`.none-option-${questionId}`);
                    if (noneCheckbox && noneCheckbox.checked) {
                        processedFormData.append(`answers[${questionId}][]`, '-1');
                    } else {
                        const values = answers[questionId].filter(v => v !== '-1' && v !== -1);
                        if (values.length > 0) {
                            values.forEach(val => {
                                processedFormData.append(`answers[${questionId}][]`, val);
                            });
                        } else {
                            // Si es un solo valor (no array), usar sin []
                            const singleValue = answers[questionId][0];
                            if (singleValue !== undefined && singleValue !== '-1' && singleValue !== -1) {
                                processedFormData.append(`answers[${questionId}]`, singleValue);
                            }
                        }
                    }
                });
                
                // Obtener botón de submit y mostrar loading
                const submitBtn = this.querySelector('button[type="submit"]');
                const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
                const btnSpinner = submitBtn ? submitBtn.querySelector('.btn-spinner') : null;
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    if (btnText) btnText.classList.add('d-none');
                    if (btnSpinner) btnSpinner.classList.remove('d-none');
                }
                
                // Obtener CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || formData.get('_token');
                
                // Enviar por AJAX
                fetch(this.action, {
                    method: 'POST',
                    body: processedFormData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    // Intentar parsear como JSON, si falla devolver texto
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => ({ success: response.ok, message: text }));
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Mostrar mensaje de éxito
                        //const message = data.message || 'Datos guardados correctamente';
                        //showNotification(message, 'success');
                        
                        // Opcional: recargar solo si es necesario (comentado para no recargar)
                        // if (data.reload !== false) {
                        //     setTimeout(() => location.reload(), 1000);
                        // }
                    } else {
                        const message = data.message || 'Error al guardar los datos';
                        showNotification(message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al guardar los datos: ' + error.message, 'error');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        if (btnText) btnText.classList.remove('d-none');
                        if (btnSpinner) btnSpinner.classList.add('d-none');
                    }
                });
            });
            });
        });
    })();
    
    // Función para mostrar notificaciones
    function showNotification(message, type) {
        // Intentar usar toastr si está disponible
        if (typeof toastr !== 'undefined') {
            if (!type === 'success') {
                toastr.error(message);
            }
            return;
        }
        
        // Si no hay toastr, usar alert o crear una notificación simple
        if (!type === 'success') {
            alert(message);
        }
    }
</script>
@endpush
@endonce
