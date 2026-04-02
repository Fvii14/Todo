<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Ayudas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="{{ asset('js/help-sidebar.js') }}"></script>
    <style>
        .help-sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        .help-sidebar.closed {
            transform: translateX(100%);
        }
        
        .help-sidebar.open {
            transform: translateX(0);
        }
        
        .help-button {
            transition: all 0.2s ease-in-out;
        }
        
        .wizard-card {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }
        
        .wizard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .wizard-card.selected {
            border-color: #3b82f6 !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
        }
        
        .wizard-card.selected.ayuda {
            border-color: #3b82f6 !important;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
        
        .wizard-card.selected.collector {
            border-color: #10b981 !important;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
        
        .wizard-form-container {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease-in-out;
        }
        
        .wizard-form-container.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .wizard-icon {
            transition: all 0.3s ease-in-out;
        }
        
        .wizard-card.selected .wizard-icon {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    @include('layouts.headerbackoffice')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <a href="{{ route('wizards.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">Crear nuevo wizard</h1>
                </div>
                <p class="text-gray-600">Selecciona el tipo de wizard que quieres crear</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="wizard-card bg-white rounded-lg shadow-md border-2 border-transparent hover:border-blue-500"
                    onclick="selectWizardType('ayuda')" id="wizard-card-ayuda">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 wizard-icon">
                            <i class="fas fa-hands-helping text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Wizard de Ayuda</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Crea una ayuda pública completa con cuestionario, preguntas y lógica de elegibilidad
                        </p>
                        <div class="text-xs text-gray-500">
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Información de la ayuda
                            </div>
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Cuestionario asociado
                            </div>
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Preguntas y condiciones
                            </div>
                            <div class="flex items-center justify-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Lógica de elegibilidad
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wizard-card bg-white rounded-lg shadow-md border-2 border-transparent hover:border-green-500"
                    onclick="selectWizardType('collector')" id="wizard-card-collector">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 wizard-icon">
                            <i class="fas fa-flag text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Wizard de Formulario Collector</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Crea nuevos formularios collector para distintas variantes de entrada
                        </p>
                        <div class="text-xs text-gray-500">
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Creación de sección de formulario
                            </div>
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Añadir preguntas a cada sección
                            </div>
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Asignar entrada del formulario
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="wizardForm" class="wizard-form-container hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Configuración del Wizard</h2>

                    <form id="createWizardForm">
                        <input type="hidden" name="type" id="wizardType">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Título del Wizard <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="title" name="title"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Ej: Ayuda para jóvenes emprendedores" required>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción (opcional)
                                </label>
                                <textarea id="description" name="description" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Describe brevemente el propósito de este wizard"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="resetForm()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <i class="fas fa-magic mr-2"></i>Crear wizard
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectWizardType(type) {
            document.querySelectorAll('.wizard-card').forEach(card => {
                card.classList.remove('selected', 'ayuda', 'collector');
            });
            
            const selectedCard = document.getElementById(`wizard-card-${type}`);
            selectedCard.classList.add('selected', type);
            
            document.getElementById('wizardType').value = type;
            
            const wizardForm = document.getElementById('wizardForm');
            wizardForm.classList.remove('hidden');
            
            setTimeout(() => {
                wizardForm.classList.add('show');
            }, 10);
            
            updateFormContent(type);

            setTimeout(() => {
                wizardForm.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300);
        }

        function resetForm() {
            document.querySelectorAll('.wizard-card').forEach(card => {
                card.classList.remove('selected', 'ayuda', 'collector');
            });
            
            const wizardForm = document.getElementById('wizardForm');
            wizardForm.classList.remove('show');
            wizardForm.classList.add('hidden');
            
            document.getElementById('createWizardForm').reset();
        }
        
        function updateFormContent(type) {
            const titleInput = document.getElementById('title');
            const descriptionTextarea = document.getElementById('description');
            const formTitle = document.querySelector('#wizardForm h2');
            
            if (type === 'ayuda') {
                titleInput.placeholder = 'Ej: Ayuda para jóvenes emprendedores';
                descriptionTextarea.placeholder = 'Describe la ayuda que se va a crear con este wizard';
                formTitle.innerHTML = 'Configuración del Wizard de Ayuda';
            } else if (type === 'collector') {
                titleInput.placeholder = 'Ej: Formulario de registro de eventos';
                descriptionTextarea.placeholder = 'Describe el formulario collector que se va a crear';
                formTitle.innerHTML = 'Configuración del Wizard de Formulario Collector';
            }
        }

        document.getElementById('createWizardForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';

            const formData = new FormData(this);
            const data = {
                type: formData.get('type'),
                title: formData.get('title'),
                description: formData.get('description'),
                data: {}
            };

            console.log('Enviando datos:', data);

            fetch('{{ route('wizards.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        window.location.href = `/admin/wizards/${data.wizard.id}`;
                    } else {
                        alert('Error al crear el wizard: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al crear el wizard: ' + error.message);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                });
        });
    </script>

    <div id="help-sidebar-app">
        <help-sidebar 
            title="Ayuda - Crear Wizard"
            main-title="Creación de Wizards"
            main-description="Esta sección te permite crear nuevos wizards. Los wizards son herramientas guiadas que te ayudan a crear ayudas complejas o automatizar procesos de forma simplificada."
            :features="[
                'Wizard de ayuda: crea ayudas completas con cuestionarios',
                'Wizard de formulario collector: crea formularios que se usarán en las distintas entradas de Collector',
                'Configuración paso a paso guiada',
                'Interfaz intuitiva y fácil de usar'
            ]"
            :steps="[
                'Selecciona el tipo de wizard que necesitas',
                'Completa la información básica del wizard',
                'Sigue el proceso guiado paso a paso',
                'Configura los detalles específicos según el tipo',
                'Finaliza y activa tu wizard'
            ]"
            additional-info='<div class="space-y-3">
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <h5 class="font-semibold text-blue-800 mb-1">🆘 Wizard de Ayuda</h5>
                    <p class="text-sm text-blue-700">Crea ayudas públicas completas con cuestionarios, preguntas y lógica de elegibilidad. Incluye información de la ayuda, cuestionario asociado, preguntas y condiciones, y lógica de elegibilidad.</p>
                </div>
                
                <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                    <h5 class="font-semibold text-green-800 mb-1">🚩 Wizard de Formulario Collector</h5>
                    <p class="text-sm text-green-700">Crea formularios que se usarán en las distintas entradas de Collector. Creando secciones y asignando preguntas a dichas secciones.</p>
                </div>
            </div>'
            important-note="Elige cuidadosamente el tipo de wizard, ya que no se puede cambiar después de la creación. El título del wizard debe ser descriptivo para facilitar su identificación posterior."
        ></help-sidebar>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.HelpSidebar.init('help-sidebar-app');
        });
    </script>
</body>

</html>
