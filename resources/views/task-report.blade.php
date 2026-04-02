<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reportar Bug o Feature - Tech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    Reportar bug o feature
                </h1>
                <p class="text-gray-600">
                    Ayúdanos a mejorar la plataforma reportando problemas o sugiriendo nuevas funcionalidades
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8" x-data="taskReportForm()">
                <form @submit.prevent="handleSubmit()" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" id="email" x-model="form.email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                        <p class="text-sm text-gray-500 mt-1">Tu email</p>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de reporte
                        </label>
                        <select id="type" x-model="form.type"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Selecciona el tipo</option>
                            <option value="bug">🐛 Bug - Error o problema en el sistema</option>
                            <option value="feature">✨ Feature - Nueva funcionalidad o mejora</option>
                        </select>
                    </div>

                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL de la página
                        </label>
                        <input type="url" id="url" x-model="form.url"
                            placeholder="https://app.tutramitefacil.es/..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required />
                    </div>

                    <div>
                        <label for="assignee" class="block text-sm font-medium text-gray-700 mb-2">
                            Persona asignada
                        </label>
                        <select id="assignee" x-model="form.assignee"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Sin asignar (por defecto)</option>
                            <option value="fcoterroba">fcoterroba</option>
                            <option value="jose">Jose</option>
                            <option value="raul">Raul</option>
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Prioridad
                        </label>
                        <select id="priority" x-model="form.priority"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Selecciona la prioridad</option>
                            <option value="baja">🟢 Baja - No urgente, puede esperar</option>
                            <option value="media">🟡 Media - Importante pero no crítica</option>
                            <option value="alta">🔴 Alta - Urgente, requiere atención inmediata</option>
                        </select>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción detallada
                        </label>
                        <textarea id="message" x-model="form.message" placeholder="Describe el bug o feature en detalle..." rows="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            required @input="form.message = $event.target.value"></textarea>
                        <p class="text-sm text-gray-500 mt-1">Caracteres: <span
                                x-text="form.message.length"></span>/2000</p>
                    </div>

                    <div class="flex space-x-4 pt-6">
                        <button type="button" @click="resetForm()"
                            class="flex-1 px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 font-medium">
                            Limpiar
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition-colors duration-200 font-medium flex items-center justify-center">
                            <span x-show="!isSubmitting">Enviar reporte</span>
                            <span x-show="isSubmitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Enviando...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Toast simple -->
                <div x-show="message" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="fixed top-4 right-4 z-50 max-w-sm">
                    <div class="p-4 rounded-lg shadow-lg"
                        :class="messageType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                        <div class="flex items-center">
                            <i :class="messageType === 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle'"
                                class="text-xl mr-2"></i>
                            <span x-text="message"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Este formulario envía la información directamente al equipo de desarrollo a través de Notion y
                    Discord.</p>
            </div>
        </div>
    </div>

    <script>
        function taskReportForm() {
            return {
                isSubmitting: false,
                message: '',
                messageType: '',
                form: {
                    email: '{{ auth()->user()->email ?? 'usuario@ejemplo.com' }}',
                    type: '',
                    url: '',
                    assignee: '',
                    priority: '',
                    message: ''
                },

                async handleSubmit() {
                    this.isSubmitting = true;
                    this.message = '';
                    this.messageType = '';

                    try {
                        const response = await fetch('/api/task-tech', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                    'content') || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.message = data.message;
                            this.messageType = 'success';
                            alert('Reporte enviado correctamente');
                            this.resetForm();
                        } else {
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).flat().join('\n');
                                this.message = `Error de validación:\n${errorMessages}`;
                            } else {
                                this.message = data.message || 'Error al enviar el formulario';
                            }
                            this.messageType = 'error';
                        }
                    } catch (error) {
                        alert('Error al enviar el formulario: ' + error);
                        this.message = 'Error al enviar el mensaje. Por favor, inténtalo de nuevo.';
                        this.messageType = 'error';
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                resetForm() {
                    this.form = {
                        email: '{{ auth()->user()->email ?? 'usuario@ejemplo.com' }}',
                        type: '',
                        url: '',
                        assignee: '',
                        priority: '',
                        message: ''
                    };
                    this.message = '';
                    this.messageType = '';
                },


                init() {
                    this.$watch('message', (value) => {
                        if (value) {
                            setTimeout(() => {
                                this.message = '';
                                this.messageType = '';
                            }, 5000);
                        }
                    });
                }
            }
        }
    </script>
</body>

</html>
