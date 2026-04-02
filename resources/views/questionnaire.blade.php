<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @extends('layouts.app')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .progress-bar {
            height: 6px;
            transition: width 0.6s ease;
        }
        .floating-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .input-focus-effect:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
        .input-cyan {
            background-color: #19fcc0;
            border: 2px solid #e5e7eb;
        }
        .input-cyan:focus {
            outline: none;
            border-color: #3c3b60;
        }
        .btn-purple-blue {
            background-color: #3c3b60;
        }
        .question-transition {
            transition: all 0.4s ease;
        }
        .question-enter {
            opacity: 0;
            transform: translateX(50px);
        }
        .question-enter-active {
            opacity: 1;
            transform: translateX(0);
        }
        .question-exit {
            opacity: 1;
            transform: translateX(0);
        }
        .question-exit-active {
            opacity: 0;
            transform: translateX(-50px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-900 min-h-screen flex items-center justify-center p-4">
    <x-gtm-noscript />
    <div class="w-full max-w-2xl bg-white rounded-2xl overflow-hidden floating-card">
        <!-- Header -->
        <div class="p-6 pb-4 border-b border-gray-200">
            <div class="mb-2 text-right">
                <span class="text-sm font-medium text-gray-600">Hola <span class="font-semibold text-indigo-600">{{ $username }}</span></span>
            </div>
            <h1 class="text-2xl font-bold text-center text-gray-800">{{ $questionnaire->name }}</h1>
            
            <!-- Progress bar -->
            <div class="mt-6">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progressBar" class="progress-bar bg-indigo-600 h-2 rounded-full" style="width: 0%"></div>
                </div>
                <div class="flex justify-between mt-1">
                    <span id="progressText" class="text-xs font-medium text-gray-500">0% completado</span>
                    <span id="questionCounter" class="text-xs font-medium text-gray-500">Pregunta 1 de {{ count($questions) }}</span>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="p-6">
        <form id="questionnaireForm" action="{{ route('answers.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            
            <div id="questionnaireContainer" class="relative min-h-[150px] overflow-hidden">
                @foreach ($questions as $index => $question)
                    <div class="question-transition absolute top-0 left-0 right-0 @if ($index > 0) opacity-0 invisible @endif" 
                        id="question_{{ $index }}"
                        data-index="{{ $index }}">
                        <div class="mb-6">
                            <input type="hidden" name="answers[{{ $question->id }}][question_id]" value="{{ $question->id }}">
                            
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $question->text }}</label>

                            @if ($question->type == 'string')
                                <input type="text" 
                                    class="input-cyan w-full px-4 py-3 rounded-lg focus:outline-none" 
                                    name="answers[{{ $question->id }}][value]" 
                                    placeholder="Escribe aquí..."
                                    autocomplete="off">

                            @elseif ($question->type == 'integer')
                                <input type="number" 
                                    class="input-cyan w-full px-4 py-3 rounded-lg focus:outline-none" 
                                    name="answers[{{ $question->id }}][value]" 
                                    placeholder="Ingresa un número..."
                                    autocomplete="off">

                            @elseif ($question->type == 'date')
                                <input type="date" 
                                    class="input-cyan w-full px-4 py-3 rounded-lg focus:outline-none" 
                                    name="answers[{{ $question->id }}][value]" 
                                    autocomplete="off">

                            @elseif ($question->type == 'boolean')
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <button type="button" 
                                            class="boolean-btn p-4 border-2 border-gray-200 rounded-lg text-base font-medium hover:border-indigo-400 focus:outline-none" 
                                            data-value="1" 
                                            data-question="{{ $question->id }}">
                                        <i class="fas fa-check-circle mr-2"></i> Sí
                                    </button>
                                    <button type="button" 
                                            class="boolean-btn p-4 border-2 border-gray-200 rounded-lg text-base font-medium hover:border-indigo-400 focus:outline-none" 
                                            data-value="0" 
                                            data-question="{{ $question->id }}">
                                        <i class="fas fa-times-circle mr-2"></i> No
                                    </button>
                                </div>
                                <input type="hidden" name="answers[{{ $question->id }}][value]" id="boolean_input_{{ $question->id }}">

                            @elseif ($question->slug == 'estado_civil')
                                <select class="input-cyan w-full px-4 py-3 rounded-lg focus:outline-none appearance-none bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMzYzNiNjAiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBjbGFzcz0ibHVjaWRlIGx1Y2lkZS1jaGV2cm9uLWRvd24iPjxwYXRoIGQ9Im02IDkgNiA2IDYtNiIvPjwvc3ZnPg==')] bg-no-repeat bg-[position:right_1rem_center] bg-[size:1.5rem]" 
                                        name="answers[{{ $question->id }}][value]">
                                    <option value="" disabled selected>Selecciona una opción</option>
                                    <option value="Soltero">Soltero/a</option>
                                    <option value="Casado">Casado/a</option>
                                    <option value="Viudo">Viudo/a</option>
                                    <option value="Divorciado">Divorciado/a</option>
                                </select>

                            @elseif ($question->slug == 'sexo')
                                <select class="input-cyan w-full px-4 py-3 rounded-lg focus:outline-none appearance-none bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMzYzNiNjAiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBjbGFzcz0ibHVjaWRlIGx1Y2lkZS1jaGV2cm9uLWRvd24iPjxwYXRoIGQ9Im02IDkgNiA2IDYtNiIvPjwvc3ZnPg==')] bg-no-repeat bg-[position:right_1rem_center] bg-[size:1.5rem]" 
                                        name="answers[{{ $question->id }}][value]">
                                    <option value="" disabled selected>Selecciona una opción</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Mujer">Mujer</option>
                                </select>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Navigation -->
            <div class="flex justify-between mt-8 pt-4 border-t border-gray-200">
                <button type="button" id="prevBtn" class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 focus:outline-none hidden">
                    <i class="fas fa-arrow-left mr-2"></i> Anterior
                </button>
                <button type="button" id="nextBtn" class="ml-auto px-6 py-2 btn-purple-blue text-white font-medium rounded-lg hover:opacity-90 focus:outline-none flex items-center">
                    Siguiente <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button type="submit" id="submitBtn" class="ml-auto px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none hidden flex items-center">
                    Enviar <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </div>
        </form>
        </div>
    </div>

    <script>
        let currentQuestion = 0;
        const totalQuestions = {{ count($questions) }};
        const formData = {};
        let isAnimating = false;

        // DOM Elements
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const questionCounter = document.getElementById('questionCounter');
        const questionnaireContainer = document.getElementById('questionnaireContainer');

        // Update progress
        function updateProgress() {
            const progress = ((currentQuestion + 1) / totalQuestions) * 100;
            progressBar.style.width = `${progress}%`;
            progressText.textContent = `${Math.round(progress)}% completado`;
            questionCounter.textContent = `Pregunta ${currentQuestion + 1} de ${totalQuestions}`;
        }

        // Update navigation buttons
        function updateNavigationButtons() {
            prevBtn.classList.toggle('hidden', currentQuestion === 0);
            nextBtn.classList.toggle('hidden', currentQuestion === totalQuestions - 1);
            submitBtn.classList.toggle('hidden', currentQuestion !== totalQuestions - 1);
        }

        // Save current answer
        function saveCurrentAnswer() {
            const questionElement = document.getElementById(`question_${currentQuestion}`);
            const inputs = questionElement.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                if (input.type !== 'hidden') {
                    formData[input.name] = input.value;
                }
            });
        }

        // Change question with animation
        function changeQuestion(direction) {
            if (isAnimating) return;
            isAnimating = true;
            
            const currentElement = document.getElementById(`question_${currentQuestion}`);
            
            // Exit animation
            currentElement.style.opacity = '0';
            currentElement.style.transform = `translateX(${direction === 'next' ? '-50px' : '50px'})`;
            
            setTimeout(() => {
                currentElement.classList.add('invisible');
                
                // Update current question
                currentQuestion += direction === 'next' ? 1 : -1;
                
                // Show new question
                const nextElement = document.getElementById(`question_${currentQuestion}`);
                nextElement.classList.remove('invisible');
                nextElement.style.opacity = '0';
                nextElement.style.transform = `translateX(${direction === 'next' ? '50px' : '-50px'})`;
                
                // Force reflow to trigger transition
                void nextElement.offsetWidth;
                
                // Enter animation
                nextElement.style.opacity = '1';
                nextElement.style.transform = 'translateX(0)';
                
                // Enfocar el primer input/select de la nueva pregunta
                setTimeout(() => {
                    const focusable = nextElement.querySelector('input, select');
                    if (focusable) focusable.focus();
                }, 100); // Pequeño delay para que coincida con la animación
                
                updateProgress();
                updateNavigationButtons();
                isAnimating = false;
            }, 200);
        }

        // Event listeners
        nextBtn.addEventListener('click', () => {
            saveCurrentAnswer();
            changeQuestion('next');
        });

        prevBtn.addEventListener('click', () => {
            changeQuestion('prev');
        });

        submitBtn.addEventListener('click', () => {
            saveCurrentAnswer();
            console.log("Respuestas completas del usuario:", formData);
            
            // Success animation
            submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Enviando...';
            submitBtn.classList.remove('bg-green-600');
            submitBtn.classList.add('bg-green-500');

            alert("Formulario completado.");
            document.getElementById('questionnaireForm').submit();
        });

        // Boolean buttons functionality
        document.querySelectorAll('.boolean-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const questionId = this.dataset.question;
                
                // Remove previous selection
                document.querySelectorAll(`.boolean-btn[data-question="${questionId}"]`).forEach(b => {
                    b.classList.remove('border-indigo-600', 'bg-indigo-50');
                });
                
                // Mark as selected
                this.classList.add('border-indigo-600', 'bg-indigo-50');
                
                // Update hidden input value
                document.querySelector(`input[name="answers[${questionId}][value]"]`).value = this.dataset.value;
            });
        });

        // Initialize
        updateProgress();
        updateNavigationButtons();

        // Función para manejar el evento keydown
        function handleKeyDown(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Evita el comportamiento por defecto (útil si es un formulario)
                
                // Si no estamos en la última pregunta, avanzar
                if (currentQuestion < totalQuestions - 1) {
                    nextBtn.click(); // Simula el click en el botón siguiente
                } else {
                    submitBtn.click(); // En la última pregunta, envía el formulario
                }
            }
        }

        // Añadir event listeners a todos los inputs y selects
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('keydown', handleKeyDown);
        });
    </script>
</body>
</html>