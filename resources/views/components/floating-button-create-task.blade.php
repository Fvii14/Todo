<div x-data="floatingNotionButton()" class="fixed bottom-6 right-6 z-50" x-cloak>
    <div class="relative group">
        <button @click="openModal()"
            class="bg-blue-600 hover:bg-blue-700 text-white rounded-full w-14 h-14 shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center relative"
            :class="{ 'animate-pulse': isOpen }">
            <span class="text-2xl font-bold">+</span>
            <div x-show="form.type"
                class="absolute -top-1 -left-1 bg-white text-blue-600 text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold shadow-md"
                x-text="form.type === 'bug' ? '🐛' : '✨'"></div>
            <div x-show="form.priority === 'alta'"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold">
                !
            </div>
        </button>

        <div
            class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-gray-800 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
            Reportar bug o feature
            <div
                class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800">
            </div>
        </div>
    </div>

    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal()"
        x-cloak style="display: none;">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300"
            @click.stop x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    Reporta un bug o feature a tech
                </h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="bx bx-x text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <form @submit.prevent="handleSubmit()" class="space-y-4">
                    <div>
                        <label for="notion-email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input type="email" id="notion-email" x-model="form.email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                            readonly />
                        <p class="text-xs text-gray-500 mt-1">Email del usuario logueado</p>
                    </div>

                    <div>
                        <label for="notion-type" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de reporte
                        </label>
                        <select id="notion-type" x-model="form.type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Selecciona el tipo</option>
                            <option value="bug">🐛 Bug - Error o problema en el sistema</option>
                            <option value="feature">✨ Feature - Nueva funcionalidad o mejora</option>
                        </select>
                    </div>

                    <div>
                        <label for="notion-url" class="block text-sm font-medium text-gray-700 mb-1">
                            URL de la página
                        </label>
                        <input type="url" id="notion-url" x-model="form.url"
                            placeholder="https://ejemplo.com/pagina"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required />
                        <p class="text-xs text-gray-500 mt-1">Puedes modificar la URL si es necesario</p>
                    </div>

                    <div>
                        <label for="notion-assignee" class="block text-sm font-medium text-gray-700 mb-1">
                            Persona asignada
                        </label>
                        <select id="notion-assignee" x-model="form.assignee"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Sin asignar (por defecto)</option>
                            <option value="fcoterroba">fcoterroba</option>
                            <option value="jose">Jose</option>
                            <option value="raul">Raul</option>
                        </select>
                    </div>

                    <div>
                        <label for="notion-priority" class="block text-sm font-medium text-gray-700 mb-1">
                            Prioridad
                        </label>
                        <select id="notion-priority" x-model="form.priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Selecciona la prioridad</option>
                            <option value="baja">🟢 Baja - No urgente, puede esperar</option>
                            <option value="media">🟡 Media - Importante pero no crítica</option>
                            <option value="alta">🔴 Alta - Urgente, requiere atención inmediata</option>
                        </select>
                    </div>

                    <div>
                        <label for="notion-message" class="block text-sm font-medium text-gray-700 mb-1">
                            Descripción detallada
                        </label>
                        <textarea id="notion-message" x-model="form.message" placeholder="Describe el bug o feature en detalle..."
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            required
                            @input="form.message = $event.target.value"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Caracteres: <span x-text="form.message.length"></span>/2000</p>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="closeModal()"
                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-md transition-colors duration-200 flex items-center justify-center">
                            <span x-show="!isSubmitting">Enviar</span>
                            <span x-show="isSubmitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
            </div>
        </div>
    </div>
</div>

<script>
    function floatingNotionButton() {
        return {
            isOpen: false,
            isSubmitting: false,
            form: {
                email: '{{ auth()->user()->email ?? 'usuario@ejemplo.com' }}',
                type: '',
                url: window.location.href,
                assignee: '',
                priority: '',
                message: ''
            },

            openModal() {
                this.isOpen = true
                this.form.url = window.location.href
                document.body.style.overflow = 'hidden'
            },

            closeModal() {
                this.isOpen = false
                document.body.style.overflow = 'auto'
                setTimeout(() => {
                    if (!this.isOpen) {
                        this.resetForm()
                    }
                }, 300)
            },

            resetForm() {
                this.form = {
                    email: '{{ auth()->user()->email ?? 'usuario@ejemplo.com' }}',
                    type: '',
                    url: window.location.href,
                    assignee: '',
                    priority: '',
                    message: ''
                }
            },

            async handleSubmit() {
                this.isSubmitting = true

                // Debug: mostrar el contenido del formulario
                console.log('Formulario a enviar:', this.form)

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
                    })

                    const data = await response.json()

                    if (data.success) {
                        alert(data.message)
                        this.closeModal()
                    } else {
                        if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat().join('\n')
                            alert(`Error de validación:\n${errorMessages}`)
                        } else {
                            alert(data.message || 'Error al enviar el formulario')
                        }
                    }
                } catch (error) {
                    console.error('Error al enviar el formulario:', error)
                    alert('Error al enviar el mensaje. Por favor, inténtalo de nuevo.')
                } finally {
                    this.isSubmitting = false
                }
            },

            init() {
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isOpen) {
                        this.closeModal()
                    }
                })
            }
        }
    }
</script>
