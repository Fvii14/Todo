<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ayudas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">
    @include('layouts.headerbackoffice')
    <div class="w-full max-w-screen-xl mx-auto px-4 py-6 space-y-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboardv2') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('ayudas.recursos') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            Recursos de la ayuda
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">Recursos de {{ $ayuda->nombre_ayuda }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-bold text-gray-800">Recursos de la ayuda: <u>{{ $ayuda->nombre_ayuda }}</u></h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @if ($recursos->count() > 0)
                @foreach ($recursos as $recurso)
                    <button type="button" class="bg-white rounded-lg shadow p-4 text-left w-full transition hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 recurso-btn" data-recurso='@json($recurso)'>
                        <h2 class="text-lg font-bold">{{ $recurso->titulo }}</h2>
                        <p class="text-gray-500 text-sm mt-1">{{ $recurso->descripcion }}</p>
                        <span class="inline-block mt-2 px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">Tipo: {{ $recurso->tipo }}</span>
                    </button>
                @endforeach
                <a href="{{ route('ayudas.recursos.create', $ayuda->id) }}" class="text-blue-500">Añadir recursos</a>
            @else
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-bold">No hay recursos para esta ayuda. <a
                            href="{{ route('ayudas.recursos.create', $ayuda->id) }}" class="text-blue-500">Añadir
                            recursos</a></h2>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de edición de recurso -->
    <div id="editRecursoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden transition-all">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-lg relative animate-fade-in-up">
            <div class="absolute top-3 right-3 flex space-x-2">
                <button onclick="desasociarRecurso()" class="text-gray-400 hover:text-orange-500 text-xl focus:outline-none" title="Desasociar recurso de esta ayuda">
                    <i class="fas fa-unlink"></i>
                </button>
                <button onclick="eliminarRecurso()" class="text-gray-400 hover:text-red-500 text-xl focus:outline-none" title="Eliminar recurso completamente">
                    <i class="fas fa-trash"></i>
                </button>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 text-2xl focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <h2 class="text-2xl font-bold mb-4 text-blue-700">Editar recurso</h2>
            <form id="editRecursoForm" class="space-y-4">
                <input type="hidden" name="id" id="recurso_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Título</label>
                    <input type="text" name="titulo" id="recurso_titulo" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="descripcion" id="recurso_descripcion" rows="2" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select name="tipo" id="recurso_tipo" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="texto">Texto</option>
                        <option value="video">Video</option>
                        <option value="imagen">Imagen</option>
                        <option value="enlace">Enlace</option>
                    </select>
                </div>
                <div id="campo_contenido_texto">
                    <label class="block text-sm font-medium text-gray-700">Contenido (texto)</label>
                    <textarea name="contenido_texto" id="recurso_contenido_texto" rows="2" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div id="campo_url_video" class="hidden">
                    <label class="block text-sm font-medium text-gray-700">URL Video</label>
                    <input type="text" name="url_video" id="recurso_url_video" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div id="campo_url_imagen" class="hidden">
                    <label class="block text-sm font-medium text-gray-700">URL Imagen</label>
                    <input type="text" name="url_imagen" id="recurso_url_imagen" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div id="campo_url_enlace" class="hidden">
                    <label class="block text-sm font-medium text-gray-700">URL Enlace</label>
                    <input type="text" name="url_enlace" id="recurso_url_enlace" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Orden</label>
                    <input type="number" name="orden" id="recurso_orden" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="activo" id="recurso_activo" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="recurso_activo" class="ml-2 text-sm text-gray-700">Activo</label>
                </div>
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-bold shadow">Guardar</button>
                </div>
            </form>
            <div id="editRecursoMsg" class="mt-4 text-center text-sm"></div>
        </div>
    </div>

    <script>
        // Asignar eventListener a los botones de recurso
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.recurso-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    openEditModal(JSON.parse(this.dataset.recurso));
                });
            });
            const tipoSelect = document.getElementById('recurso_tipo');
            tipoSelect.addEventListener('change', function() {
                mostrarCamposPorTipo(this.value);
            });
        });
        function openEditModal(recurso) {
            document.getElementById('editRecursoModal').classList.remove('hidden');
            document.getElementById('recurso_id').value = recurso.id;
            document.getElementById('recurso_titulo').value = recurso.titulo;
            document.getElementById('recurso_descripcion').value = recurso.descripcion;
            document.getElementById('recurso_tipo').value = recurso.tipo;
            document.getElementById('recurso_contenido_texto').value = recurso.contenido_texto || '';
            document.getElementById('recurso_url_video').value = recurso.url_video || '';
            document.getElementById('recurso_url_imagen').value = recurso.url_imagen || '';
            document.getElementById('recurso_url_enlace').value = recurso.url_enlace || '';
            document.getElementById('recurso_orden').value = recurso.orden || 0;
            document.getElementById('recurso_activo').checked = recurso.activo ? true : false;
            mostrarCamposPorTipo(recurso.tipo);
            document.getElementById('editRecursoMsg').innerHTML = '';
        }
        function closeEditModal() {
            document.getElementById('editRecursoModal').classList.add('hidden');
        }
        
        function desasociarRecurso() {
            const id = document.getElementById('recurso_id').value;
            const titulo = document.getElementById('recurso_titulo').value;
            
            if (confirm(`¿Estás seguro de que quieres desasociar el recurso "${titulo}" de esta ayuda? El recurso seguirá existiendo y podrá ser usado en otras ayudas.`)) {
                fetch(`/admin/ayudas/recursos/${id}/desasociar`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        document.getElementById('editRecursoMsg').innerHTML = '<span class="text-green-600 font-semibold">¡Recurso desasociado correctamente!</span>';
                        setTimeout(() => { location.reload(); }, 1000);
                    } else {
                        document.getElementById('editRecursoMsg').innerHTML = '<span class="text-red-600 font-semibold">Error al desasociar el recurso</span>';
                    }
                })
                .catch(err => {
                    document.getElementById('editRecursoMsg').innerHTML = '<span class="text-red-600 font-semibold">Error de red. Motivo: ' + err.message + '</span>';
                });
            }
        }
        
        function eliminarRecurso() {
            const id = document.getElementById('recurso_id').value;
            const titulo = document.getElementById('recurso_titulo').value;
            
            if (confirm(`⚠️ ADVERTENCIA: ¿Estás seguro de que quieres eliminar completamente el recurso "${titulo}"? Esta acción eliminará el recurso de TODAS las ayudas y no se puede deshacer.`)) {
                fetch(`/admin/ayudas/recursos/${id}/eliminar`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        document.getElementById('editRecursoMsg').innerHTML = '<span class="text-green-600 font-semibold">¡Recurso eliminado completamente!</span>';
                        setTimeout(() => { location.reload(); }, 1000);
                    } else {
                        document.getElementById('editRecursoMsg').innerHTML = '<span class="text-red-600 font-semibold">Error al eliminar el recurso</span>';
                    }
                })
                .catch(err => {
                    document.getElementById('editRecursoMsg').innerHTML = '<span class="text-red-600 font-semibold">Error de red. Motivo: ' + err.message + '</span>';
                });
            }
        }
        
        function mostrarCamposPorTipo(tipo) {
            document.getElementById('campo_contenido_texto').classList.add('hidden');
            document.getElementById('campo_url_video').classList.add('hidden');
            document.getElementById('campo_url_imagen').classList.add('hidden');
            document.getElementById('campo_url_enlace').classList.add('hidden');
            if (tipo === 'texto') {
                document.getElementById('campo_contenido_texto').classList.remove('hidden');
            } else if (tipo === 'video') {
                document.getElementById('campo_url_video').classList.remove('hidden');
            } else if (tipo === 'imagen') {
                document.getElementById('campo_url_imagen').classList.remove('hidden');
            } else if (tipo === 'enlace') {
                document.getElementById('campo_url_enlace').classList.remove('hidden');
            }
        }
        document.getElementById('editRecursoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('recurso_id').value;
            const data = {
                titulo: document.getElementById('recurso_titulo').value,
                descripcion: document.getElementById('recurso_descripcion').value,
                tipo: document.getElementById('recurso_tipo').value,
                contenido_texto: document.getElementById('recurso_contenido_texto').value,
                url_video: document.getElementById('recurso_url_video').value,
                url_imagen: document.getElementById('recurso_url_imagen').value,
                url_enlace: document.getElementById('recurso_url_enlace').value,
                orden: document.getElementById('recurso_orden').value,
                activo: document.getElementById('recurso_activo').checked ? 1 : 0
            };
            try {
                const response = await fetch(`/admin/ayudas/recursos/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('editRecursoMsg').innerHTML = '<span class="text-green-600 font-semibold">¡Guardado correctamente!</span>';
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    document.getElementById('editRecursoMsg').innerHTML = '<span class="text-red-600 font-semibold">Error al guardar</span>';
                }
            } catch (err) {
                document.getElementById('editRecursoMsg').innerHTML = '<span class="text-red-600 font-semibold">Error de red. Motivo: ' + err.message + '</span>';
            }
        });
    </script>
</body>

</html>
