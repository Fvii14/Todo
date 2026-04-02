<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tickets - Backoffice</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
@include('layouts.headerbackoffice')

<div class="min-h-screen bg-gray-50 py-8">
    <div class="w-19/20 mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#54debd] to-[#368e79]">Gestión de Tickets</span>
            </h1>
            <p class="text-gray-600">Administra y resuelve los reportes de problemas de los usuarios</p>
            <div class="mt-4">
                <button type="button" 
                        onclick="cargarEstadisticas()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#54debd] to-[#43c5a9] text-white text-sm font-medium rounded-lg hover:from-[#43c5a9] hover:to-[#54debd] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#54debd] transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Estadísticas
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Tickets</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $tickets->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pendientes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $tickets->where('estado', 'pendiente')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-search text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">En Revisión</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $tickets->where('estado', 'en_revision')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Resueltos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $tickets->where('estado', 'resuelto')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2 mb-4 sm:mb-0">
                    <a href="{{ route('admin.tickets.index') }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ !$estado ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200' }}">
                        <i class="fas fa-list mr-2"></i>
                        Todos
                    </a>
                    <a href="{{ route('admin.tickets.index', ['estado' => 'pendiente']) }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $estado === 'pendiente' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200' }}">
                        <i class="fas fa-clock mr-2"></i>
                        Pendientes
                    </a>
                    <a href="{{ route('admin.tickets.index', ['estado' => 'en_revision']) }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $estado === 'en_revision' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200' }}">
                        <i class="fas fa-search mr-2"></i>
                        En Revisión
                    </a>
                    <a href="{{ route('admin.tickets.index', ['estado' => 'resuelto']) }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $estado === 'resuelto' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200' }}">
                        <i class="fas fa-check mr-2"></i>
                        Resueltos
                    </a>
                </div>
                <div class="text-sm text-gray-500">
                    Mostrando {{ $tickets->count() }} de {{ $tickets->total() }} tickets
                </div>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <div class="h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-40">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ticket
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Error
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Navegador
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sistema
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        #{{ $ticket->id }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-xs font-medium text-white">
                                                {{ strtoupper(substr($ticket->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $ticket->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $ticket->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-md">
                                    <a href="{{ $ticket->url_error }}" target="_blank" 
                                       class="text-xs text-indigo-600 hover:text-indigo-900 truncate block">
                                        {{ Str::limit($ticket->url_error, 50) }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $ticket->navegador }}</div>
                                @if($ticket->version_navegador)
                                    <div class="text-xs text-gray-500">{{ $ticket->version_navegador }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $ticket->so }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ticket->estado === 'pendiente')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1 text-xs"></i>
                                        {{ $ticket->estado_texto }}
                                    </span>
                                @elseif($ticket->estado === 'en_revision')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-search mr-1 text-xs"></i>
                                        {{ $ticket->estado_texto }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1 text-xs"></i>
                                        {{ $ticket->estado_texto }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm">{{ $ticket->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $ticket->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" 
                                       class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                                       title="Ver detalles">
                                        <i class="fas fa-eye mr-1 text-xs"></i>
                                        Ver
                                    </a>
                                    
                                    <div class="relative inline-block text-left">
                                        <button type="button" 
                                                onclick="toggleDropdown({{ $ticket->id }})"
                                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                                            <i class="fas fa-cog mr-1 text-xs"></i>
                                            Acciones
                                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                        </button>
                                        
                                        <div id="dropdown-{{ $ticket->id }}" 
                                             class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                            <div class="py-1" role="menu">
                                                <a href="#" 
                                                   onclick="cambiarEstado({{ $ticket->id }}, 'pendiente')"
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                                                   role="menuitem">
                                                    <i class="fas fa-clock text-yellow-500 mr-3"></i>
                                                    Marcar como Pendiente
                                                </a>
                                                <a href="#" 
                                                   onclick="cambiarEstado({{ $ticket->id }}, 'en_revision')"
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                                                   role="menuitem">
                                                    <i class="fas fa-search text-blue-500 mr-3"></i>
                                                    Marcar en Revisión
                                                </a>
                                                <a href="#" 
                                                   onclick="cambiarEstado({{ $ticket->id }}, 'resuelto')"
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                                                   role="menuitem">
                                                    <i class="fas fa-check text-green-500 mr-3"></i>
                                                    Marcar como Resuelto
                                                </a>
                                                <div class="border-t border-gray-100"></div>
                                                <a href="#" 
                                                   onclick="eliminarTicket({{ $ticket->id }})"
                                                   class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200"
                                                   role="menuitem">
                                                    <i class="fas fa-trash mr-3"></i>
                                                    Eliminar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay tickets</h3>
                                    <p class="text-gray-500 text-sm">No se encontraron tickets para mostrar con los filtros actuales.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($tickets->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrando {{ $tickets->firstItem() ?? 0 }} a {{ $tickets->lastItem() ?? 0 }} de {{ $tickets->total() }} resultados
            </div>
            <div class="flex justify-center">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal de estadísticas -->
<div id="estadisticasModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>
                    Estadísticas de Tickets
                </h3>
                <button onclick="closeEstadisticasModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="estadisticasContent" class="space-y-4">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para mostrar/ocultar dropdowns
function toggleDropdown(ticketId) {
    const dropdown = document.getElementById(`dropdown-${ticketId}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
    
    // Cerrar todos los otros dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${ticketId}`) {
            d.classList.add('hidden');
        }
    });
    
    // Toggle del dropdown actual
    dropdown.classList.toggle('hidden');
}

// Cerrar dropdowns al hacer clic fuera
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target) && !event.target.closest('[onclick*="toggleDropdown"]')) {
            dropdown.classList.add('hidden');
        }
    });
});

function cambiarEstado(ticketId, nuevoEstado) {
    if (!confirm('¿Estás seguro de que quieres cambiar el estado de este ticket?')) {
        return;
    }

    fetch(`/admin/tickets/${ticketId}/estado`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar notificación de éxito
            showNotification('Estado actualizado correctamente', 'success');
            // Recargar la página después de un breve delay
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error al cambiar el estado: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al cambiar el estado del ticket', 'error');
    });
}

function eliminarTicket(ticketId) {
    if (!confirm('¿Estás seguro de que quieres eliminar este ticket? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch(`/admin/tickets/${ticketId}`, {
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
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error al eliminar el ticket: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar el ticket', 'error');
    });
}

function cargarEstadisticas() {
    const modal = document.getElementById('estadisticasModal');
    modal.classList.remove('hidden');

    fetch('/admin/tickets/estadisticas')
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('estadisticasContent');
            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="text-center">
                            <div class="text-2xl font-bold">${data.total}</div>
                            <div class="text-sm opacity-90">Total de Tickets</div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
                        <div class="text-center">
                            <div class="text-2xl font-bold">${data.pendientes}</div>
                            <div class="text-sm opacity-90">Pendientes</div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg p-4 text-white">
                        <div class="text-center">
                            <div class="text-2xl font-bold">${data.en_revision}</div>
                            <div class="text-sm opacity-90">En Revisión</div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                        <div class="text-center">
                            <div class="text-2xl font-bold">${data.resueltos}</div>
                            <div class="text-sm opacity-90">Resueltos</div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('estadisticasContent').innerHTML = `
                <div class="text-center text-red-600">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p>En construcción</p>
                </div>
            `;
        });
}

function closeEstadisticasModal() {
    document.getElementById('estadisticasModal').classList.add('hidden');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
    
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