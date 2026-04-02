<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $ticket->id }} - Backoffice</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    @include('layouts.headerbackoffice')

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="w-19/20 mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('admin.tickets.index') }}"
                            class="mr-4 p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                                <span
                                    class="text-transparent bg-clip-text bg-gradient-to-r from-[#54debd] to-[#368e79]">Ticket
                                    #{{ $ticket->id }}</span>
                            </h1>
                            <p class="mt-2 text-gray-600">Creado el
                                {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="relative inline-block text-left">
                            <button type="button" onclick="toggleActionsDropdown()"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fas fa-cog mr-2"></i>
                                Acciones
                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </button>

                            <div id="actionsDropdown"
                                class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                <div class="py-1" role="menu">
                                    <a href="#" onclick="cambiarEstado('pendiente')"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                                        role="menuitem">
                                        <i class="fas fa-clock text-yellow-500 mr-3"></i>
                                        Marcar como Pendiente
                                    </a>
                                    <a href="#" onclick="cambiarEstado('en_revision')"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                                        role="menuitem">
                                        <i class="fas fa-search text-blue-500 mr-3"></i>
                                        Marcar en Revisión
                                    </a>
                                    <a href="#" onclick="cambiarEstado('resuelto')"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                                        role="menuitem">
                                        <i class="fas fa-check text-green-500 mr-3"></i>
                                        Marcar como Resuelto
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <a href="#" onclick="eliminarTicket()"
                                        class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200"
                                        role="menuitem">
                                        <i class="fas fa-trash mr-3"></i>
                                        Eliminar Ticket
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Ticket Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Ticket Details Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                                Información del Ticket
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2">Estado:</label>
                                    <div>
                                        @if ($ticket->estado === 'pendiente')
                                            <span id="estadoBadge"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-2"></i>
                                                {{ $ticket->estado_texto }}
                                            </span>
                                        @elseif($ticket->estado === 'en_revision')
                                            <span id="estadoBadge"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-search mr-2"></i>
                                                {{ $ticket->estado_texto }}
                                            </span>
                                        @else
                                            <span id="estadoBadge"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-2"></i>
                                                {{ $ticket->estado_texto }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">URL
                                        del Error:</label>
                                    <div>
                                        <a href="{{ $ticket->url_error }}" target="_blank"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm break-all">
                                            {{ $ticket->url_error }}
                                        </a>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2">Navegador:</label>
                                    <div class="text-sm text-gray-900">{{ $ticket->navegador }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2">Versión
                                        del Navegador:</label>
                                    <div class="text-sm text-gray-900">
                                        {{ $ticket->version_navegador ?: 'No especificada' }}</div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2">Sistema
                                        Operativo:</label>
                                    <div class="text-sm text-gray-900">{{ $ticket->so }}</div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2">Fecha
                                        de Creación:</label>
                                    <div class="text-sm text-gray-900">
                                        {{ $ticket->created_at->format('d/m/Y H:i:s') }}</div>
                                </div>

                                @if ($ticket->updated_at != $ticket->created_at)
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2">Última
                                            Actualización:</label>
                                        <div class="text-sm text-gray-900">
                                            {{ $ticket->updated_at->format('d/m/Y H:i:s') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Problem Description Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-comment text-yellow-600 mr-2"></i>
                                Descripción del Problema
                            </h2>
                        </div>
                        <div class="p-6">
                            @if ($ticket->descripcion)
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">
                                        {{ $ticket->descripcion }}</p>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div
                                        class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-comment-slash text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 italic">No se proporcionó descripción
                                        del problema.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- User Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-user text-indigo-600 mr-2"></i>
                                Usuario que Reportó
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="text-center mb-6">
                                <div
                                    class="w-20 h-20 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-white font-bold text-2xl">
                                        {{ strtoupper(substr($ticket->user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    {{ $ticket->user->name }}</h3>
                                <p class="text-gray-600">{{ $ticket->user->email }}</p>
                            </div>

                            <div class="space-y-3">
                                {{-- DESACOPLADO: enlace a panel-usuario --}}
                                {{-- <a href="{{ route('admin.panel-usuario', $ticket->user) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                <i class="fas fa-eye mr-2"></i>
                                Ver Perfil
                            </a> --}}
                                <a href="mailto:{{ $ticket->user->email }}"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                    <i class="fas fa-envelope mr-2"></i>
                                    Enviar Email
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- History Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-history text-indigo-600 mr-2"></i>
                                Historial
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-3 h-3 bg-indigo-500 rounded-full mt-2"></div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="text-sm font-medium text-gray-900">Ticket
                                            Creado</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $ticket->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>

                                @if ($ticket->updated_at != $ticket->created_at)
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full mt-2">
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="text-sm font-medium text-gray-900">Última
                                                Actualización</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $ticket->updated_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar/ocultar dropdown de acciones
        function toggleActionsDropdown() {
            const dropdown = document.getElementById('actionsDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('actionsDropdown');
            if (!dropdown.contains(event.target) && !event.target.closest(
                    '[onclick*="toggleActionsDropdown"]')) {
                dropdown.classList.add('hidden');
            }
        });

        function cambiarEstado(nuevoEstado) {
            if (!confirm('¿Estás seguro de que quieres cambiar el estado de este ticket?')) {
                return;
            }

            fetch('{{ route('admin.tickets.update-estado', $ticket) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        estado: nuevoEstado
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar el badge de estado
                        const badge = document.getElementById('estadoBadge');
                        let newClass = '';
                        let icon = '';
                        let text = '';

                        switch (nuevoEstado) {
                            case 'pendiente':
                                newClass =
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800';
                                icon = '<i class="fas fa-clock mr-2"></i>';
                                text = 'Pendiente';
                                break;
                            case 'en_revision':
                                newClass =
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800';
                                icon = '<i class="fas fa-search mr-2"></i>';
                                text = 'En Revisión';
                                break;
                            case 'resuelto':
                                newClass =
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
                                icon = '<i class="fas fa-check mr-2"></i>';
                                text = 'Resuelto';
                                break;
                        }

                        badge.className = newClass;
                        badge.innerHTML = icon + text;

                        // Mostrar notificación de éxito
                        showNotification('Estado actualizado correctamente', 'success');
                    } else {
                        showNotification('Error al cambiar el estado: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al cambiar el estado del ticket', 'error');
                });
        }

        function eliminarTicket() {
            if (!confirm(
                    '¿Estás seguro de que quieres eliminar este ticket? Esta acción no se puede deshacer.'
                )) {
                return;
            }

            fetch('{{ route('admin.tickets.destroy', $ticket) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Ticket eliminado correctamente', 'success');
                        // Redirigir a la lista de tickets después de un breve delay
                        setTimeout(() => {
                            window.location.href = '{{ route('admin.tickets.index') }}';
                        }, 1000);
                    } else {
                        showNotification('Error al eliminar el ticket: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al eliminar el ticket', 'error');
                });
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className =
                `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;

            if (type === 'success') {
                notification.className += ' bg-green-500 text-white';
                notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>${message}</span>
            </div>
        `;
            } else {
                notification.className += ' bg-red-500 text-white';
                notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>${message}</span>
            </div>
        `;
            }

            document.body.appendChild(notification);

            // Animar entrada
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Remover después de 3 segundos
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    </script>
</body>

</html>
