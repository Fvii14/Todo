<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Simulación de Usuarios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .info-chip {
            transition: all 0.2s ease;
        }

        .info-chip:hover {
            transform: scale(1.05);
        }

        .section-divider {
            border-top: 1px dashed #e2e8f0;
        }

        .help-toggle {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .help-toggle.active {
            max-height: 1000px;
        }

        .rotate-90 {
            transform: rotate(90deg);
        }

        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .animate-float {
            animation: float 1s ease-in-out infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-700 {
            animation-delay: 0.7s;
        }

        .delay-800 {
            animation-delay: 0.8s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-3px);
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">

    @include('layouts.headerbackoffice')
    
    <div id="app" class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Simulación de Usuarios</h1>
                <p class="mt-2 text-gray-600">Simula la experiencia de usuarios normales en la plataforma</p>
            </div>
            
            <div v-if="simulatedUser" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-secret text-yellow-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Simulando como: @{{ simulatedUser.name }}
                        </h3>
                        <p class="text-sm text-yellow-700">@{{ simulatedUser.email }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="stopSimulation" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-stop mr-1"></i>Detener simulación
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <div v-if="alert" class="mb-6">
            <div :class="alertClass" class="rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i v-if="alert.type === 'success'" class="fas fa-check-circle text-green-400 text-lg"></i>
                        <i v-else class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p :class="alertTextClass" class="text-sm font-medium">@{{ alert.message }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="alert = null" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de búsqueda -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-6">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input 
                            v-model="searchQuery" 
                            @input="debounceSearch"
                            type="text" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Buscar por nombre, email o ID..."
                        >
                    </div>
                </div>
                <button 
                    @click="performSearch"
                    :disabled="loading"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-search mr-2"></i>
                    Buscar
                </button>
                <button 
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <i class="fas fa-times mr-2"></i>
                    Limpiar
                </button>
            </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="bg-white shadow-md rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Usuarios disponibles para simular</h3>
                <p class="mt-1 text-sm text-gray-500">Selecciona un usuario para simular su experiencia</p>
            </div>
            
            <div v-if="loading" class="p-8 text-center">
                <i class="fas fa-spinner fa-spin text-blue-600 text-4xl mb-4"></i>
                <p class="text-sm text-gray-500">Cargando usuarios...</p>
            </div>
            
            <div v-else-if="users.length === 0" class="p-8 text-center">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No se encontraron usuarios</h3>
                <p class="text-gray-500">No hay usuarios que coincidan con tu búsqueda.</p>
            </div>
            
            <div v-else class="overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    <li v-for="user in users" :key="user.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">@{{ user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900">@{{ user.name }}</p>
                                        <span v-if="user.id === (simulatedUser?.id)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-circle text-yellow-400 mr-1"></i>
                                            Simulando
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500">@{{ user.email }}</p>
                                    <div class="flex items-center space-x-4 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            ID: @{{ user.id }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-comment mr-1"></i>
                                            @{{ user.answers_count }} respuestas
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Registrado: @{{ formatDate(user.created_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button 
                                    @click="startSimulation(user)"
                                    :disabled="user.id === (simulatedUser?.id)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <i class="fas fa-play mr-1"></i>
                                    @{{ user.id === (simulatedUser?.id) ? 'Simulando' : 'Simular' }}
                                </button>
                                <button 
                                    @click="viewUser(user)"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                >
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
                
                <!-- Paginación -->
                <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button 
                                @click="changePage(pagination.current_page - 1)"
                                :disabled="pagination.current_page === 1"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Anterior
                            </button>
                            <button 
                                @click="changePage(pagination.current_page + 1)"
                                :disabled="pagination.current_page === pagination.last_page"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Siguiente
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando <span class="font-medium">@{{ pagination.from }}</span> a <span class="font-medium">@{{ pagination.to }}</span> de <span class="font-medium">@{{ pagination.total }}</span> resultados
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <button 
                                        v-for="page in pagination.links" 
                                        :key="page.label"
                                        @click="changePage(page.url ? new URL(page.url).searchParams.get('page') : null)"
                                        :disabled="!page.url || page.active"
                                        :class="page.active ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                        v-html="page.label"
                                    ></button>
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    users: [],
                    simulatedUser: null,
                    searchQuery: '',
                    loading: false,
                    alert: null,
                    pagination: null,
                    searchTimeout: null
                }
            },
            computed: {
                alertClass() {
                    return this.alert?.type === 'success' 
                        ? 'bg-green-50 border border-green-200' 
                        : 'bg-red-50 border border-red-200';
                },
                alertTextClass() {
                    return this.alert?.type === 'success' 
                        ? 'text-green-800' 
                        : 'text-red-800';
                }
            },
            mounted() {
                this.loadUsers();
                this.loadSimulatedUser();
                this.checkForAlerts();
            },
            methods: {
                async loadUsers(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page: page,
                            ...(this.searchQuery && { search: this.searchQuery })
                        });
                        
                        const response = await fetch(`/admin/simulation/search?${params}`);
                        const data = await response.json();
                        
                        this.users = data.users.data;
                        this.pagination = data.users;
                    } catch (error) {
                        this.showAlert('Error al cargar usuarios', 'error');
                    } finally {
                        this.loading = false;
                    }
                },
                
                async loadSimulatedUser() {
                    try {
                        const response = await fetch('/admin/simulation/status');
                        const data = await response.json();
                        this.simulatedUser = data.simulatedUser;
                    } catch (error) {
                        console.error('Error loading simulated user:', error);
                    }
                },
                
                checkForAlerts() {
                    // Check for session alerts
                    const successMessage = '{{ session("success") }}';
                    const errorMessage = '{{ session("error") }}';
                    
                    if (successMessage) {
                        this.showAlert(successMessage, 'success');
                    }
                    if (errorMessage) {
                        this.showAlert(errorMessage, 'error');
                    }
                },
                
                debounceSearch() {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.performSearch();
                    }, 300);
                },
                
                async performSearch() {
                    await this.loadUsers(1);
                },
                
                clearSearch() {
                    this.searchQuery = '';
                    this.performSearch();
                },
                
                async startSimulation(user) {
                    if (confirm(`¿Iniciar simulación como ${user.name}?`)) {
                        try {
                            const response = await fetch(`/admin/simulation/start/${user.id}`, {
                                method: 'GET',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            });
                            
                            if (response.ok) {
                                this.showAlert(`Simulando como: ${user.name}`, 'success');
                                this.simulatedUser = user;
                                // Redirect to user home
                                window.location.href = '/home';
                            } else {
                                const data = await response.json();
                                this.showAlert(data.message || 'Error al iniciar simulación', 'error');
                            }
                        } catch (error) {
                            this.showAlert('Error al iniciar simulación', 'error');
                        }
                    }
                },
                
                async stopSimulation() {
                    try {
                        const response = await fetch('/admin/simulation/stop', {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            this.showAlert('Simulación detenida', 'success');
                            this.simulatedUser = null;
                            // Redirect to admin dashboard
                            window.location.href = '/dashboardv2';
                        } else {
                            const data = await response.json();
                            this.showAlert(data.message || 'Error al detener simulación', 'error');
                        }
                    } catch (error) {
                        this.showAlert('Error al detener simulación', 'error');
                    }
                },
                
                viewUser(user) {
                    window.open(`/home?simulate=${user.id}`, '_blank');
                },
                
                async changePage(page) {
                    if (page) {
                        await this.loadUsers(page);
                    }
                },
                
                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                },
                
                showAlert(message, type = 'success') {
                    this.alert = { message, type };
                    setTimeout(() => {
                        this.alert = null;
                    }, 5000);
                }
            }
        }).mount('#app');
    </script>
</body>

</html> 