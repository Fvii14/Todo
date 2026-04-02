<div class="modal fade" id="convivienteModal" tabindex="-1" aria-labelledby="convivienteModalLabel" aria-hidden="true">
    <style>
        /* Fondo del modal */
        #convivienteModal .modal-content {
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            border: none;
            padding: 20px;
        }

        /* Título */
        #convivienteModal .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3c3a60;
        }

        /* Cada pregunta */
        #convivienteModal .question-item {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        /* Label de la pregunta */
        #convivienteModal .form-label {
            font-weight: 600;
            color: #333;
        }

        /* Botón guardar más grande y vistoso */
        #convivienteModal .modal-footer .btn-primary {
            background-color: #54debd;
            border-color: #54debd;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 12px 24px;
        }

        #convivienteModal .modal-footer .btn-primary:hover {
            background-color: #40d4b0;
        }

        /* En móvil: que el botón esté sticky abajo */
        @media (max-width: 768px) {
            #convivienteModal .modal-footer {
                position: sticky;
                bottom: 0;
                background: white;
                padding: 1rem;
                border-top: 1px solid #eee;
                z-index: 10;
            }
        }

        .bg-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 6px;
            padding: 12px;
        }
    </style>

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="convivienteForm" method="POST" action="{{ route('conviviente.store') }}">
                @csrf
                <input type="hidden" name="index" value="{{ $convivienteIndex }}">
                <input type="hidden" name="questionnaire_id" value="{{ $questionnaireId }}">

                <div class="modal-header">
                    <h5 class="modal-title">
                        @if (!empty($convivienteNombre))
                            Añadir datos {{ $convivienteNombre }}
                        @else
                            Añadir datos conviviente #{{ $convivienteIndex }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body" id="convivienteModalBody">
                    @foreach ($questions as $question)
                        @php
                            $esObligatoria = true;
                            $answer = $question['answer'] ?? null;
                            
                            // Para preguntas boolean, considerar faltante solo si es null, false, o string vacío (no respondida)
                            // Si es '0' o '1' (string) o 0 o 1 (número), está respondida (No o Sí respectivamente)
                            if ($question['type'] === 'boolean') {
                                // Una pregunta boolean está faltante si no tiene respuesta (null, false, o string vacío)
                                // Pero NO está faltante si tiene '0' o '1' (que son respuestas válidas: No o Sí)
                                $respuestaFaltante = (
                                    is_null($answer) || 
                                    $answer === '' || 
                                    $answer === false
                                ) && $esObligatoria;
                            } else {
                                $respuestaFaltante =
                                    (is_null($answer) ||
                                        $answer === '' ||
                                        $answer === [] ||
                                        $answer == -1) &&
                                    $esObligatoria;
                            }
                        @endphp

                        <div class="question-item mb-3 {{ $respuestaFaltante ? 'bg-warning bg-opacity-25' : '' }}"
                            data-id="{{ $question['id'] }}">

                            <label class="form-label">
                                {{ $question['text'] }}
                                @if ($esObligatoria)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @if (!empty($question['subtext']))
                                <div class="text-muted small mb-1">{{ $question['subtext'] }}</div>
                            @endif

                            @if ($question['type'] === 'string')
                                <input type="text" name="answers[{{ $question['id'] }}]"
                                    value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                    class="form-control" pattern="{{ $question['validation']['pattern'] ?? '' }}"
                                    title="{{ $question['validation']['error_message'] ?? '' }}">
                            @elseif ($question['type'] === 'date')
                                <input type="date" name="answers[{{ $question['id'] }}]"
                                    value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                    class="form-control">
                            @elseif ($question['type'] === 'boolean')
                                <input type="hidden" name="answers[{{ $question['id'] }}]" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                        name="answers[{{ $question['id'] }}]" value="1"
                                        @if (old('answers.' . $question['id'], $question['answer']) == 1) checked @endif>
                                    <label class="form-check-label"> No / Sí</label>
                                </div>
                            @elseif ($question['type'] === 'select' && !empty($question['options']))
                                <select name="answers[{{ $question['id'] }}]" class="form-select">
                                    <option value="-1">Seleccione una opción</option>
                                    @foreach ($question['options'] as $key => $option)
                                        @php
                                            $value = is_numeric($key) ? $option : $key;
                                            $selected = old('answers.' . $question['id'], $question['answer']);
                                        @endphp
                                        <option value="{{ $value }}"
                                            @if ($selected == $value) selected @endif>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif ($question['type'] === 'multiple' && !empty($question['options']))
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
                                    $selectedKeys = [];
                                    foreach ($selectedOptions as $selectedValue) {
                                        // Si es -1, agregarlo directamente
                                        if ($selectedValue === -1 || $selectedValue === '-1') {
                                            $selectedKeys[] = -1;
                                            continue;
                                        }
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
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input type="checkbox" 
                                            class="form-check-input multiple-checkbox-{{ $question['id'] }}"
                                            data-question-id="{{ $question['id'] }}"
                                            name="answers[{{ $question['id'] }}][]" 
                                            value="{{ $key }}"
                                            {{ in_array($key, $selectedKeys) ? 'checked' : '' }}
                                            {{ $hasNoneSelected ? 'disabled' : '' }}>
                                        <label class="form-check-label">{{ $option }}</label>
                                    </div>
                                @endforeach
                                {{-- Opción "Ninguna de las anteriores" --}}
                                <div class="form-check mt-2" style="border-top: 1px solid #dee2e6; padding-top: 0.5rem;">
                                    <input type="checkbox" 
                                        class="form-check-input none-option-{{ $question['id'] }}"
                                        data-question-id="{{ $question['id'] }}"
                                        name="answers[{{ $question['id'] }}][]" 
                                        value="-1"
                                        {{ $hasNoneSelected ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold">Ninguna de las anteriores</label>
                                </div>
                            @elseif ($question['type'] === 'date')
                                <input type="date" name="answers[{{ $question['id'] }}]"
                                    value="{{ $question['answer'] ?? '' }}" class="form-control"
                                    placeholder="{{ $question['subtext'] ?? '' }}">
                             @elseif ($question['type'] === 'integer')
                                <input type="number" name="answers[{{ $question['id'] }}]"
                                    value="{{ old('answers.' . $question['id'], $question['answer']) }}"
                                    class="form-control" min="0" step="1"
                                    placeholder="{{ $question['subtext'] ?? '' }}">
                            @elseif ($question['type'] === 'builder' && ($question['slug'] ?? '') === 'calculadora')
                                @php
                                    $calculadoraData = null;
                                    if (!empty($question['answer'])) {
                                        try {
                                            $calculadoraData = is_string($question['answer']) ? json_decode($question['answer'], true) : $question['answer'];
                                        } catch (Exception $e) {
                                            $calculadoraData = null;
                                        }
                                    }
                                @endphp
                                <div id="calculadora-{{ $question['id'] }}" class="calculadora-container" data-question-id="{{ $question['id'] }}"></div>
                                <input type="hidden" name="answers[{{ $question['id'] }}]" id="calculadora-{{ $question['id'] }}-data" value="{{ old('answers.' . $question['id'], $question['answer'] ?? '') }}" class="calculadora-input">
                            @elseif ($question['type'] === 'builder')
                                <div class="text-warning">[Builder "{{ $question['slug'] ?? 'desconocido' }}" no implementado]</div>
                            @else
                                <div class="text-danger">[Tipo de pregunta "{{ $question['type'] }}" no implementado]
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>


                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar datos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
if (!window.calculadoras) {
    window.calculadoras = {};
}

window.initCalculadorasEnModal = function() {
    console.log('⚠️ initCalculadorasEnModal llamada pero aún no implementada');
};

(function() {
    'use strict';
    // Manejo de "Ninguna de las anteriores" para preguntas múltiples
    function initMultipleNoneOption() {
        function handleNoneOption(questionId, isChecked) {
            const regularCheckboxes = document.querySelectorAll(`.multiple-checkbox-${questionId}`);
            
            if (isChecked) {
                regularCheckboxes.forEach(cb => {
                    cb.checked = false;
                    cb.disabled = true;
                });
            } else {
                regularCheckboxes.forEach(cb => {
                    cb.disabled = false;
                });
            }
        }

        function handleRegularOption(questionId) {
            const noneCheckbox = document.querySelector(`.none-option-${questionId}`);
            if (noneCheckbox && noneCheckbox.checked) {
                noneCheckbox.checked = false;
                handleNoneOption(questionId, false);
            }
        }

        document.querySelectorAll('[class*="none-option-"]').forEach(checkbox => {
            const questionId = checkbox.getAttribute('data-question-id');
            if (!checkbox.hasAttribute('data-listener-added')) {
                checkbox.setAttribute('data-listener-added', 'true');
                checkbox.addEventListener('change', function() {
                    handleNoneOption(questionId, this.checked);
                });
            }
        });

        document.querySelectorAll('[class*="multiple-checkbox-"]').forEach(checkbox => {
            const questionId = checkbox.getAttribute('data-question-id');
            if (!checkbox.hasAttribute('data-listener-added')) {
                checkbox.setAttribute('data-listener-added', 'true');
                checkbox.addEventListener('change', function() {
                    handleRegularOption(questionId);
                });
            }
        });
    }
    
    // Inicializar cuando el modal se muestra
    const modal = document.getElementById('convivienteModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            setTimeout(initMultipleNoneOption, 100);
        });
    }
    
    // También inicializar si el modal ya está visible
    if (modal && modal.classList.contains('show')) {
        setTimeout(initMultipleNoneOption, 100);
    }

    if (!window.calculadoras) {
        window.calculadoras = {};
    }

    window.initCalculadora = function(questionId, initialData = null) {
        console.log(`🎯 initCalculadora llamado para questionId: ${questionId}`, initialData);
        const container = document.getElementById(`calculadora-${questionId}`);
        if (!container) {
            console.error(`❌ No se encontró el contenedor calculadora-${questionId}`);
            return;
        }

        if (window.calculadoras && window.calculadoras[questionId]) {
            console.log(`🔄 Reinicializando calculadora ${questionId}`);
            delete window.calculadoras[questionId];
        }

        const incomes = (initialData?.incomes || []).map(inc => ({ ...inc }));
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount || 0);
        }
        
        function calculateTotals() {
            totalMonths = incomes.reduce((sum, inc) => sum + (inc.months || 0), 0);
            const totalGrossIncome = incomes.reduce((sum, inc) => sum + (inc.annual || 0), 0);
            const estimatedDeductions = totalGrossIncome * 0.15; // 15% de deducciones estimadas
            const netIncome = totalGrossIncome - estimatedDeductions;
            
            return { totalMonths, totalGrossIncome, estimatedDeductions, netIncome };
        }
        
        function render() {
            const { totalMonths, totalGrossIncome, estimatedDeductions, netIncome } = calculateTotals();
            const remainingMonths = Math.max(0, 12 - totalMonths);
            
            container.innerHTML = `
                <div class="bg-white rounded-lg p-4 mb-6 border border-gray-200">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-sm text-gray-700">Meses utilizados (últimos 12)</span>
                            <span class="text-sm fw-medium ${remainingMonths === 0 ? 'text-danger' : 'text-dark'}">${totalMonths} / 12</span>
                        </div>
                        <div class="w-100" style="height: 8px; background-color: #e9ecef; border-radius: 4px; overflow: hidden;">
                            <div class="h-100 transition-all ${totalMonths < 9 ? 'bg-success' : (totalMonths < 12 ? 'bg-warning' : 'bg-danger')}" 
                                 style="width: ${Math.min(100, Math.round((totalMonths/12)*100))}%"></div>
                        </div>
                        <div class="mt-1 text-xs ${remainingMonths === 0 ? 'text-danger' : 'text-muted'}">
                            ${remainingMonths === 0 ? 'Has alcanzado el límite de 12 meses' : `Te quedan ${remainingMonths} mes(es) disponibles`}
                        </div>
                    </div>
                    
                    <div class="mb-4 p-3 bg-info bg-opacity-10 border border-info rounded">
                        <p class="text-sm text-info">
                            Introduce tus ingresos brutos de los <strong>últimos 12 meses</strong>. Puedes repartirlos por tipo (Trabajo, Pensión, Prestación, etc.), pero el <strong>total de meses no puede superar 12</strong>.
                        </p>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-medium text-gray-700">Tipo de ingreso</label>
                            <select id="calc-type-${questionId}" class="form-select form-select-sm">
                                <option value="">Seleccionar...</option>
                                <option value="Trabajo">Trabajo</option>
                                <option value="Pensión">Pensión</option>
                                <option value="Prestación">Prestación</option>
                                <option value="Renta">Renta</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-medium text-gray-700">Meses percibidos</label>
                            <input type="number" id="calc-months-${questionId}" min="1" ${remainingMonths > 0 ? `max="${remainingMonths}"` : ''} 
                                   class="form-control form-control-sm"
                                   placeholder="12" ${remainingMonths === 0 ? 'disabled' : ''}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-medium text-gray-700">Importe medio</label>
                            <input type="number" id="calc-amount-${questionId}" step="0.01" min="0" 
                                   class="form-control form-control-sm"
                                   placeholder="1200">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-medium text-gray-700 d-block">&nbsp;</label>
                            <button onclick="addIncomeModal(${questionId})" 
                                    ${remainingMonths === 0 ? 'disabled' : ''}
                                    class="btn btn-success btn-sm w-100 ${remainingMonths === 0 ? 'disabled' : ''}">
                                ➕ Añadir ingreso
                            </button>
                        </div>
                    </div>
                    
                    ${incomes.length > 0 ? `
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 bg-light border-bottom">
                                <div class="row g-3 text-sm fw-medium text-gray-700">
                                    <div class="col-3">Tipo</div>
                                    <div class="col-2">Meses</div>
                                    <div class="col-3">Importe medio</div>
                                    <div class="col-3">Importe anual</div>
                                    <div class="col-1"></div>
                                </div>
                            </div>
                            <div class="border-top">
                                ${incomes.map((income, index) => `
                                    <div class="px-4 py-3 border-bottom">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-3">
                                                <span class="badge bg-primary">${income.type}</span>
                                            </div>
                                            <div class="col-2">
                                                <span class="badge bg-secondary">${income.months} meses</span>
                                            </div>
                                            <div class="col-3 text-sm">${formatCurrency(income.amount)}</div>
                                            <div class="col-3 text-sm fw-medium">${formatCurrency(income.annual)}</div>
                                            <div class="col-1 text-end">
                                                <button onclick="removeIncomeModal(${questionId}, ${index})" class="btn btn-sm btn-link text-danger p-0">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                                <div class="px-4 py-3 bg-light d-flex justify-content-end align-items-center gap-4">
                                    <div class="text-sm text-gray-700">
                                        Total anual: <span class="fw-semibold">${formatCurrency(totalGrossIncome)}</span>
                                    </div>
                                    <div class="text-sm text-muted">Deducciones estimadas: ${formatCurrency(estimatedDeductions)}</div>
                                    <div class="text-sm fw-semibold">Neto estimado: ${formatCurrency(netIncome)}</div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }
        
        function addIncome(questionId) {
            const type = document.getElementById(`calc-type-${questionId}`).value;
            const months = parseInt(document.getElementById(`calc-months-${questionId}`).value);
            const amount = parseFloat(document.getElementById(`calc-amount-${questionId}`).value);
            
            if (!type || !months || !amount) {
                alert('⚠️ Por favor, completa todos los campos del ingreso.');
                return;
            }
            
            const { totalMonths } = calculateTotals();
            if (totalMonths + months > 12) {
                alert('⚠️ El total de meses no puede superar 12.');
                return;
            }
            
            incomes.push({
                type: type,
                months: months,
                amount: amount,
                annual: amount * months
            });
            
            document.getElementById(`calc-type-${questionId}`).value = '';
            document.getElementById(`calc-months-${questionId}`).value = '';
            document.getElementById(`calc-amount-${questionId}`).value = '';
            
            render();
            updateHiddenInput(questionId);
        }
        
        function removeIncome(questionId, index) {
            incomes.splice(index, 1);
            render();
            updateHiddenInput(questionId);
        }
        
        function updateHiddenInput(questionId) {
            const data = {
                incomes: incomes,
                totalGrossIncome: calculateTotals().totalGrossIncome,
                estimatedDeductions: calculateTotals().estimatedDeductions,
                netIncome: calculateTotals().netIncome
            };
            const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(data);
            }
        }
        
        window.calculadoras[questionId] = {
            incomes: incomes,
            addIncome: (qId) => addIncome(qId),
            removeIncome: (qId, idx) => removeIncome(qId, idx),
            getData: () => {
                const { totalGrossIncome, estimatedDeductions, netIncome } = calculateTotals();
                return {
                    incomes: incomes,
                    totalGrossIncome: totalGrossIncome,
                    estimatedDeductions: estimatedDeductions,
                    netIncome: netIncome
                };
            }
        };
        
        render();
        updateHiddenInput(questionId);
        console.log(`✅ Calculadora ${questionId} inicializada correctamente`);
    };

    const initCalculadora = window.initCalculadora;

    window.addIncomeModal = function(questionId) {
        if (window.calculadoras[questionId]) {
            window.calculadoras[questionId].addIncome(questionId);
        }
    };
    
    window.removeIncomeModal = function(questionId, index) {
        if (window.calculadoras[questionId]) {
            window.calculadoras[questionId].removeIncome(questionId, index);
        }
    };

    window.initCalculadorasEnModal = function() {
        console.log('🔧 Inicializando calculadoras...');
        const modalBody = document.getElementById('convivienteModalBody');
        if (!modalBody) {
            console.log('❌ No se encontró convivienteModalBody');
            return;
        }
        
        const containers = modalBody.querySelectorAll('.calculadora-container');
        console.log(`📦 Encontrados ${containers.length} contenedores de calculadora`);
        
        if (containers.length === 0) {
            console.log('❌ No hay contenedores de calculadora');
            return;
        }
        
        containers.forEach(container => {
            const questionId = container.getAttribute('data-question-id');
            console.log(`🔍 Procesando calculadora para questionId: ${questionId}`);
            
            if (!questionId) {
                console.log('❌ No hay questionId');
                return;
            }

            const containerContent = container.innerHTML.trim();
            if (window.calculadoras && window.calculadoras[questionId] && containerContent !== '' && containerContent !== '<!-- La calculadora se renderizará aquí con JavaScript -->') {
                console.log(`✅ Calculadora ${questionId} ya está inicializada`);
                return;
            }
            
            const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
            let initialData = null;
            
            if (hiddenInput && hiddenInput.value) {
                try {
                    initialData = JSON.parse(hiddenInput.value);
                    console.log('📊 Datos iniciales encontrados:', initialData);
                } catch (e) {
                    console.error('❌ Error parsing calculadora data:', e);
                }
            }
            
            console.log(`🚀 Inicializando calculadora para questionId: ${questionId}`);
            initCalculadora(questionId, initialData);
        });
    };

    const modalElement = document.getElementById('convivienteModal');
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function() {
            console.log('👁️ Modal mostrado, inicializando calculadoras...');
            setTimeout(function() {
                window.initCalculadorasEnModal();
            }, 300);
        });
        if (modalElement.classList.contains('show')) {
            console.log('👁️ Modal ya visible, inicializando calculadoras...');
            setTimeout(function() {
                window.initCalculadorasEnModal();
            }, 300);
        }
    }
    setTimeout(function() {
        console.log('⏰ Timeout: intentando inicializar calculadoras...');
        window.initCalculadorasEnModal();
    }, 500);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM cargado, inicializando...');
            setTimeout(initMultipleNoneOption, 100);
            setTimeout(function() {
                window.initCalculadorasEnModal();
            }, 500);
        });
    } else {
        console.log('📄 DOM ya listo, inicializando...');
        setTimeout(initMultipleNoneOption, 100);
        setTimeout(function() {
            window.initCalculadorasEnModal();
        }, 500);
    }
    console.log('📜 Script del modal cargado, ejecutando inicialización...');
    setTimeout(function() {
        if (window.initCalculadorasEnModal) {
            console.log('✅ Ejecutando initCalculadorasEnModal desde el script...');
            window.initCalculadorasEnModal();
        } else {
            console.error('❌ initCalculadorasEnModal no está disponible');
        }
    }, 100);
})();
</script>